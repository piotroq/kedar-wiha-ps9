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

namespace Przelewy24\Payment\Payment;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\CardData\CardData;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Means;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\ReferenceNumber;
use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRegister;
use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Configuration\Enum\SessionSpecialMethodTypeEnum;
use Przelewy24\Configuration\Enum\StatusDriverEnum;
use Przelewy24\Cookie\LastPaymentMethod\LastPaymentMethodCookie;
use Przelewy24\Dto\SessionIdConfig;
use Przelewy24\Exceptions\AgreementMissingException;
use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Dto\Przelewy24Transaction;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Dto\AfterPaymentAction;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;
use Przelewy24\Payment\Przelewy24PaymentAbstract;
use Przelewy24\Payment\Voter\PaymentVoter;
use Przelewy24\Payment\Voter\RegisterTransactionResultVoter;
use Przelewy24\Provider\Payload\TransactionRegisterPayloadProvider;
use Przelewy24\Translator\Adapter\Translator;

class CardPayment extends Przelewy24PaymentAbstract implements Przelewy24PaymentInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    private $urlReturnFront;

    public function __construct(
        Przelewy24Config $config,
        ConnectionFactory $connectionFactory,
        PaymentVoter $paymentVoter,
        RegisterTransactionResultVoter $paymentResultVoter,
        TransactionRegisterPayloadProvider $payloadProvider,
        \PaymentModule $module,
        AmountExtraChargeCalculator $chargeCalculator,
        LastPaymentMethodCookie $lastPaymentMethodCookie,
        Translator $translator,
        UrlHelper $urlHelper
    ) {
        parent::__construct(
            $config,
            $connectionFactory,
            $paymentVoter,
            $paymentResultVoter,
            $payloadProvider,
            $module,
            $chargeCalculator,
            $lastPaymentMethodCookie,
            $translator
        );

        $this->urlHelper = $urlHelper;
    }

    public function getId(): int
    {
        $paymentMethod = $this->config->getPayment()->getPaymentMethodNameList()->getPaymentMethodByType(PaymentTypeEnum::CARD_PAYMENT);
        if ($paymentMethod === null) {
            return -1;
        }

        return $paymentMethod->getId();
    }

    public function isCurrentPayment(int $idPayment): bool
    {
        if ($idPayment !== $this->getId() || !$this->config->getCards()->getPaymentInStore()) {
            return false;
        }

        return true;
    }

    public function pay()
    {
        try {
            $this->validateCartAgreements();

            return parent::pay();
        } catch (FrontMessageException $fe) {
            $this->errors[] = $fe->getFrontMessage();
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return $this->afterPaymentAction();
    }

    protected function validateCartAgreements()
    {
        if (!isset($this->params['p24_regulation_accept'])
            || !$this->params['p24_regulation_accept']
            || $this->params['p24_regulation_accept'] === 'false') {
            $this->validation = false;
            throw new AgreementMissingException('Agreement is required', $this->translator->trans('Agreement is required', [], 'Modules.Przelewy24payment.Exception'));
        }

        if (isset($this->params['conditions_to_approve'])) {
            foreach ($this->params['conditions_to_approve'] as $condition) {
                if ($condition !== 'true') {
                    $this->validation = false;
                    throw new AgreementMissingException('Agreement is required', $this->translator->trans('Agreement is required', [], 'Modules.Przelewy24payment.Exception'));
                }
            }
        }
    }

    protected function configureSessionId(): SessionIdConfig
    {
        $this->config->getCards()->getClickToPay();
        $this->config->getCards()->getOneClickCard();

        return new SessionIdConfig($this->params['id_payment'], $this->cart->id, $this->getSpecialMethod());
    }

    private function getSpecialMethod()
    {
        if ($this->config->getCards()->getClickToPay()) {
            return SessionSpecialMethodTypeEnum::CARD_IN_STORE_CLICK_TO_PAY;
        }

        if ($this->config->getCards()->getOneClickCard()) {
            return SessionSpecialMethodTypeEnum::CARD_IN_STORE_ONE_CLICK;
        }

        return SessionSpecialMethodTypeEnum::CARD_IN_STORE;
    }

    protected function buildPayload(): TransactionRegister
    {
        $transactionRegister = parent::buildPayload();
        $cardData = new CardData();
        $means = new Means();
        $reference = new ReferenceNumber();
        $reference->setId($this->params['ref_id']);
        $means->setReferenceNumber($reference);
        $cardData->setMeans($means);
        $cardData->setTransactionType($this->params['transaction_type']);
        $transactionRegister->setUrlNotify($this->urlHelper->getUrlStatus(StatusDriverEnum::CARD_STATUS));
        $this->urlReturnFront = $transactionRegister->getUrlReturn();

        return $transactionRegister->setCardData($cardData);
    }

    protected function buildPayloadInitial(): TransactionRegister
    {
        $transactionRegister = $this->buildPayload();
        $this->urlReturnFront = $this->urlHelper->getUrlCardsControllerWithInformation();
        $transactionRegister->setAmount(0);
        $transactionRegister->setDescription('Initial card: ' . $this->params['ref_id']);
        $transactionRegister->calculateSign($this->config->getCredentials()->getSalt());

        return $transactionRegister;
    }

    protected function addTransactionToDb(TransactionRegister $transactionRegister)
    {
        $transaction = new Przelewy24Transaction();
        $transaction->setSessionId($transactionRegister->getSessionId());
        $transaction->setSessionHash($transactionRegister->getSessionId()->getHash());
        $transaction->setAmount($transactionRegister->getAmount());
        $transaction->setMerchantId($transactionRegister->getMerchantId());
        $transaction->setIdPayment($transactionRegister->getMethod());
        $transaction->setShopId($transactionRegister->getPosId());
        $transaction->setAmount($transactionRegister->getAmount());
        $transaction->setIdCart($this->cart->id);
        $transaction->setIdAccount($this->config->getAccount()->id);
        $transaction->setTestMode((bool) $this->config->getAccount()->test_mode);
        $transaction->setSaveCard((bool) ($this->params['transaction_type'] == 'initial'));
        $transaction->setCrc($this->config->getCredentials()->getSalt());
        $transaction->setIsoCurrency($this->config->getAccount()->getIsoCurrency());

        $id_order = \Order::getIdByCartId($this->cart->id);
        $id_order = $id_order ? $id_order : null;
        $transaction->setPsIdOrder($id_order);

        return Przlewy24AccountModel::addTransaction($transaction);
    }

    protected function afterPaymentAction()
    {
        $afterPaymentAction = new AfterPaymentAction();
        if (!empty($this->errors)) {
            $afterPaymentAction->setErrors($this->errors);

            return $afterPaymentAction;
        }
        $data = $this->response->getData();
        $token = $data['token'];
        $jsonData = ['token' => $token, 'urlReturn' => $this->urlReturnFront];

        return $afterPaymentAction->setParams($jsonData);
    }

    public function initialPayment()
    {
        try {
            $this->validation = true;
            $this->validateCartAgreements();
            $this->registerTransaction($this->buildPayloadInitial());
            $this->validateTransaction();
        } catch (FrontMessageException $fe) {
            $this->errors[] = $fe->getFrontMessage();
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return $this->afterPaymentAction();
    }
}
