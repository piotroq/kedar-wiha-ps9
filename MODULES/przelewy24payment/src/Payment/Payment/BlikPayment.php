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

use Przelewy24\Api\Przelewy24\Dto\Body\Additional\Additional;
use Przelewy24\Api\Przelewy24\Dto\Body\Additional\Psu\Psu;
use Przelewy24\Api\Przelewy24\Dto\Body\BlikChargeByCode;
use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRegister;
use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Request\BlikChargeByCodeRequest;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Configuration\Enum\SessionSpecialMethodTypeEnum;
use Przelewy24\Configuration\Enum\StatusDriverEnum;
use Przelewy24\Cookie\LastPaymentMethod\LastPaymentMethodCookie;
use Przelewy24\Dto\SessionIdConfig;
use Przelewy24\Exceptions\AgreementMissingException;
use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Exceptions\InvalidBlikCodeException;
use Przelewy24\Helper\Server\ServerHelper;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Payment\Dto\AfterPaymentAction;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;
use Przelewy24\Payment\Przelewy24PaymentAbstract;
use Przelewy24\Payment\Voter\BlikChargeByCodeResultVoter;
use Przelewy24\Payment\Voter\PaymentVoter;
use Przelewy24\Payment\Voter\RegisterTransactionResultVoter;
use Przelewy24\Provider\Payload\TransactionRegisterPayloadProvider;
use Przelewy24\Translator\Adapter\Translator;

class BlikPayment extends Przelewy24PaymentAbstract implements Przelewy24PaymentInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    private $urlReturnFront;
    private $urlBlikAjax;
    protected $chargeByCodeResponse;
    private $chargeByCodeResultVoter;

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
        UrlHelper $urlHelper,
        BlikChargeByCodeResultVoter $chargeByCodeResultVoter
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

        $this->chargeByCodeResultVoter = $chargeByCodeResultVoter;
    }

    public function getId(): int
    {
        $paymentMethod = $this->config->getPayment()->getPaymentMethodNameList()->getPaymentMethodByType(PaymentTypeEnum::BLIK_LEVEL_O_PAYMENT);
        if ($paymentMethod === null) {
            return -1;
        }

        return $paymentMethod->getId();
    }

    public function isCurrentPayment(int $idPayment): bool
    {
        if ($idPayment !== $this->getId()) {
            return false;
        }

        return true;
    }

    public function pay()
    {
        try {
            $this->validateCartAgreements();
            $this->validateBlikCode();
            $this->checkIsValidated();
            $this->registerTransaction($this->buildPayload());
            $this->validateTransaction();
            $this->chargeByCode();
            $this->validateChargeByCode();
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
        return new SessionIdConfig($this->params['id_payment'], $this->cart->id, SessionSpecialMethodTypeEnum::BLIK_LVL0);
    }

    protected function buildPayload(): TransactionRegister
    {
        $transactionRegister = parent::buildPayload();
        $additional = new Additional();
        $psu = new Psu();
        $psu->setUserAgent(ServerHelper::getUserAgent());
        $psu->setIP(ServerHelper::getIp());
        $additional->setPSU($psu);
        $transactionRegister->setAdditional($additional);
        $this->urlReturnFront = $transactionRegister->getUrlReturn();
        $this->urlBlikAjax = $this->urlHelper->getBlikAjaxCheckUrl($transactionRegister->getSessionId()->getHash());

        return $transactionRegister->setUrlCardPaymentNotification($this->urlHelper->getUrlStatus(StatusDriverEnum::BLIK_STATUS));
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
        $jsonData = ['token' => $token, 'urlReturn' => $this->urlReturnFront, 'urlBlikAjax' => $this->urlBlikAjax];

        return $afterPaymentAction->setParams($jsonData);
    }

    protected function chargeByCode()
    {
        $chargeByCode = new BlikChargeByCode();
        $chargeByCode->setToken($this->response->getData()['token']);
        $chargeByCode->setBlikCode($this->params['p24_blik_code']);
        $request = new BlikChargeByCodeRequest($chargeByCode);
        $this->chargeByCodeResponse = $this->connection->sendRequest($request);
    }

    protected function validateBlikCode()
    {
        if (!isset($this->params['p24_blik_code'])
            || !preg_match('/^\\d{6}$/', $this->params['p24_blik_code'])
        ) {
            $this->validation = false;
            throw new InvalidBlikCodeException('Blik code is invalid', $this->translator->trans('Blik code is invalid', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    protected function validateChargeByCode()
    {
        $this->chargeByCodeResultVoter->voteResult($this->chargeByCodeResponse);
    }
}
