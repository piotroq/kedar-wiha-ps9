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
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\XPayPayload;
use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRegister;
use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Cookie\LastPaymentMethod\LastPaymentMethodCookie;
use Przelewy24\Exceptions\AgreementMissingException;
use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Payment\Dto\AfterPaymentAction;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;
use Przelewy24\Payment\Przelewy24PaymentAbstract;
use Przelewy24\Payment\Voter\PaymentVoter;
use Przelewy24\Payment\Voter\RegisterTransactionResultVoter;
use Przelewy24\Provider\Payload\TransactionRegisterPayloadProvider;
use Przelewy24\Translator\Adapter\Translator;

class ApplePayPayment extends Przelewy24PaymentAbstract implements Przelewy24PaymentInterface
{
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
        Translator $translator
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
    }

    public function getId(): int
    {
        $paymentMethod = $this->config->getPayment()->getPaymentMethodNameList()->getPaymentMethodByType(PaymentTypeEnum::APPLE_PAYMENT);
        if ($paymentMethod === null) {
            return -1;
        }

        return $paymentMethod->getId();
    }

    public function isCurrentPayment(int $idPayment): bool
    {
        if ($idPayment !== $this->getId() || !$this->config->getApple()->getOneClick()) {
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

    protected function buildPayload(): TransactionRegister
    {
        $transactionRegister = parent::buildPayload();
        $cardData = new CardData();
        $means = new Means();
        $xPayPayload = new XPayPayload();
        $xPayPayload->setType(XPayPayload::APPLE_PAY);
        $xPayPayload->setPayload(base64_encode($this->params['payload']));
        $means->setXPayPayload($xPayPayload);
        $cardData->setMeans($means);
        $this->urlReturnFront = $transactionRegister->getUrlReturn();

        return $transactionRegister->setCardData($cardData);
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
}
