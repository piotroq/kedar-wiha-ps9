<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Payment;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRegister;
use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Request\RegisterTransactionRequest;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Cookie\LastPaymentMethod\LastPaymentMethodCookie;
use Przelewy24\Dto\SessionIdConfig;
use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Exceptions\PaymentMethodNotValidException;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Dto\Przelewy24Transaction;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Dto\AfterPaymentAction;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;
use Przelewy24\Payment\Voter\PaymentVoter;
use Przelewy24\Payment\Voter\RegisterTransactionResultVoter;
use Przelewy24\Provider\Payload\TransactionRegisterPayloadProvider;
use Przelewy24\Translator\Adapter\Translator;

abstract class Przelewy24PaymentAbstract implements Przelewy24PaymentInterface
{
    protected $cart;

    protected $customer;

    protected $config;

    protected $connection;

    protected $params = [];

    protected $response;

    protected $validation = false;

    protected $connectionFactory;

    /**
     * @var PaymentVoter
     */
    protected $paymentVoter;

    /**
     * @var TransactionRegisterPayloadProvider
     */
    protected $payloadProvider;

    /**
     * @var RegisterTransactionResultVoter
     */
    protected $paymentResultVoter;

    /**
     * @var \PaymentModule
     */
    protected $module;

    /**
     * @var AmountExtraChargeCalculator
     */
    protected $chargeCalculator;

    protected $lastPaymentMethodCookie;

    /**
     * @var Translator
     */
    protected $translator;

    protected $errors = [];
    /**
     * @var ?Przelewy24Transaction
     */
    protected $transaction;

    public function __construct(
        Przelewy24Config $config,
        ConnectionFactory $connectionFactory,
        PaymentVoter $paymentVoter,
        RegisterTransactionResultVoter $paymentResultVoter,
        TransactionRegisterPayloadProvider $payloadProvider,
        \PaymentModule $module,
        AmountExtraChargeCalculator $chargeCalculator,
        LastPaymentMethodCookie $lastPaymentMethodCookie,
        Translator $translator
    ) {
        $this->config = $config;
        $this->connectionFactory = $connectionFactory;
        $this->paymentVoter = $paymentVoter;
        $this->payloadProvider = $payloadProvider;
        $this->paymentResultVoter = $paymentResultVoter;
        $this->module = $module;
        $this->chargeCalculator = $chargeCalculator;
        $this->lastPaymentMethodCookie = $lastPaymentMethodCookie;
        $this->translator = $translator;
    }

    public function setCart(\Cart $cart)
    {
        $this->cart = $cart;
        $this->customer = new \Customer($this->cart->id_customer);
    }

    public function getCart(): ?\Cart
    {
        return $this->cart;
    }

    public function setConfig(Przelewy24Config $config)
    {
        $this->config = $config;
    }

    public function getConfig(): Przelewy24Config
    {
        return $this->config;
    }

    public function createConnection()
    {
        $this->connection = $this->connectionFactory->factory($this->config->getAccount());
    }

    public function setExtraParams(array $params)
    {
        $this->params = $params;
        $this->params['id_payment'] = $this->params['id_payment'] ?? 0;
        $this->lastPaymentMethodCookie->addIdPayment((int) $this->params['id_payment']);
    }

    public function getExtraParams(): array
    {
        return $this->params;
    }

    public function validatePaymentMethod()
    {
        $this->validation = false;
        $this->paymentVoter->vote($this);
        $this->validation = true;
    }

    protected function checkIsValidated()
    {
        if (!$this->validation) {
            throw new PaymentMethodNotValidException('Payment method is not valid', $this->translator->trans('Payment method is not valid', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    protected function configureSessionId(): SessionIdConfig
    {
        return new SessionIdConfig($this->params['id_payment'], $this->cart->id);
    }

    public function pay()
    {
        try {
            $this->checkIsValidated();
            $this->createOrder();
            $this->registerTransaction($this->buildPayload());
            $this->validateTransaction();
        } catch (FrontMessageException $fe) {
            $this->errors[] = $fe->getFrontMessage();
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return $this->afterPaymentAction();
    }

    protected function buildPayload(): TransactionRegister
    {
        return $this->payloadProvider->buildPayload(
            $this->params['id_payment'],
            $this->cart,
            $this->config,
            isset($this->params['p24_regulation_accept']) && $this->params['p24_regulation_accept'],
            $this->configureSessionId()
        );
    }

    protected function validateTransaction()
    {
        $this->paymentResultVoter->voteResult($this->response);
    }

    protected function registerTransaction(TransactionRegister $transactionRegister)
    {
        $this->checkIsValidated();
        $request = new RegisterTransactionRequest($transactionRegister);
        $this->addTransactionToDb($transactionRegister);
        $this->response = $this->connection->sendRequest($request);

        return $this->response;
    }

    protected function createOrder()
    {
        $this->checkIsValidated();
        if (!\Order::getIdByCartId($this->cart->id)) {
            $this->module->validateOrder(
                (int) $this->cart->id,
                (int) $this->config->getState()->getIdStateBeforePayment(),
                $this->cart->getOrderTotal(),
                $this->module->displayName,
                null,
                [],
                (int) $this->cart->id_currency,
                false,
                $this->customer->secure_key
            );
            $this->chargeCalculator->addAmountToOrder($this->config->getExtraCharge(), $this->cart);
        }
    }

    protected function afterPaymentAction()
    {
        $afterPaymentAction = new AfterPaymentAction();
        $data = $this->response->getData();
        $token = $data['token'];
        if (!empty($this->errors)) {
            $afterPaymentAction->setErrors($this->errors);

            return $afterPaymentAction;
        }

        return $afterPaymentAction->setRedirect($this->connection->getUrlTrnRequest((string) $token));
    }

    protected function addTransactionToDb(TransactionRegister $transactionRegister)
    {
        $transaction = new Przelewy24Transaction();
        $transaction->setSessionId($transactionRegister->getSessionId()->getSessionId());
        $transaction->setSessionHash($transactionRegister->getSessionId()->getHash());
        $transaction->setAmount($transactionRegister->getAmount());
        $transaction->setMerchantId($transactionRegister->getMerchantId());
        $transaction->setIdPayment($transactionRegister->getMethod());
        $transaction->setShopId($transactionRegister->getPosId());
        $transaction->setAmount($transactionRegister->getAmount());
        $transaction->setIdCart($this->cart->id);
        $transaction->setIdAccount($this->config->getAccount()->id);
        $transaction->setTestMode((bool) $this->config->getAccount()->test_mode);
        $transaction->setCrc($this->config->getCredentials()->getSalt());
        $transaction->setIsoCurrency($this->config->getAccount()->getIsoCurrency());
        $id_order = \Order::getIdByCartId($this->cart->id);
        $id_order = $id_order ? $id_order : null;
        $transaction->setPsIdOrder($id_order);
        $this->transaction = $transaction;

        return Przlewy24AccountModel::addTransaction($transaction);
    }
}
