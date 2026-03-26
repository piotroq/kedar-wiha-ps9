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

namespace Przelewy24\Model\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\FormTypeEnum;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\AppleConfigurationProvider;
use Przelewy24\Provider\Configuration\BlikConfigurationProvider;
use Przelewy24\Provider\Configuration\CardsConfigurationProvider;
use Przelewy24\Provider\Configuration\CredentialsConfigurationProvider;
use Przelewy24\Provider\Configuration\ExtraChargeConfigurationProvider;
use Przelewy24\Provider\Configuration\GoogleConfigurationProvider;
use Przelewy24\Provider\Configuration\InstallmentsConfigurationProvider;
use Przelewy24\Provider\Configuration\OrderConfigurationProvider;
use Przelewy24\Provider\Configuration\PaymentConfigurationProvider;
use Przelewy24\Provider\Configuration\StateConfigurationProvider;
use Przelewy24\Provider\Configuration\TimeConfigurationProvider;

class Przelewy24Config
{
    private $account;

    private $credentials;

    private $state;

    private $order;
    private $installments;

    private $time;

    private $extraCharge;

    private $payment;

    private $cards;

    private $google;

    private $apple;
    private $blik;

    /**
     * @var ExtraChargeConfigurationProvider
     */
    private $extraChargeConfigurationProvider;

    /**
     * @var OrderConfigurationProvider
     */
    private $orderConfigurationProvider;

    /**
     * @var PaymentConfigurationProvider
     */
    private $paymentConfigurationProvider;

    /**
     * @var StateConfigurationProvider
     */
    private $stateConfigurationProvider;

    /**
     * @var TimeConfigurationProvider
     */
    private $timeConfigurationProvider;

    /**
     * @var CredentialsConfigurationProvider
     */
    private $credentialsConfigurationProvider;

    /**
     * @var AppleConfigurationProvider
     */
    private $appleConfigurationProvider;

    /**
     * @var CardsConfigurationProvider
     */
    private $cardsConfigurationProvider;

    /**
     * @var GoogleConfigurationProvider
     */
    private $googleConfigurationProvider;
    /**
     * @var InstallmentsConfigurationProvider
     */
    private $installmentsConfigurationProvider;
    /**
     * @var BlikConfigurationProvider
     */
    private $blikConfigurationProvider;

    public function __construct(
        TimeConfigurationProvider $timeConfigurationProvider,
        ExtraChargeConfigurationProvider $extraChargeConfigurationProvider,
        OrderConfigurationProvider $orderConfigurationProvider,
        InstallmentsConfigurationProvider $installmentsConfigurationProvider,
        StateConfigurationProvider $stateConfigurationProvider,
        PaymentConfigurationProvider $paymentConfigurationProvider,
        CredentialsConfigurationProvider $credentialsConfigurationProvider,
        AppleConfigurationProvider $appleConfigurationProvider,
        GoogleConfigurationProvider $googleConfigurationProvider,
        CardsConfigurationProvider $cardsConfigurationProvider,
        BlikConfigurationProvider $blikConfigurationProvider
    ) {
        $this->extraChargeConfigurationProvider = $extraChargeConfigurationProvider;
        $this->orderConfigurationProvider = $orderConfigurationProvider;
        $this->installmentsConfigurationProvider = $installmentsConfigurationProvider;
        $this->paymentConfigurationProvider = $paymentConfigurationProvider;
        $this->stateConfigurationProvider = $stateConfigurationProvider;
        $this->timeConfigurationProvider = $timeConfigurationProvider;
        $this->credentialsConfigurationProvider = $credentialsConfigurationProvider;
        $this->appleConfigurationProvider = $appleConfigurationProvider;
        $this->cardsConfigurationProvider = $cardsConfigurationProvider;
        $this->googleConfigurationProvider = $googleConfigurationProvider;
        $this->blikConfigurationProvider = $blikConfigurationProvider;
    }

    public function getCredentials(): ?CredentialsConfig
    {
        return $this->credentials;
    }

    /**
     * @return Przelewy24Config
     */
    public function setCredentials(CredentialsConfig $credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getAccount(): ?Przlewy24AccountModel
    {
        return $this->account;
    }

    public function setAccount(Przlewy24AccountModel $account, $fillPayments = true, $fillCerts = false, $excludePayments = false)
    {
        $this->account = $account;
        $this->setTime($this->timeConfigurationProvider->getConfiguration($this->account));
        $this->setExtraCharge($this->extraChargeConfigurationProvider->getConfiguration($this->account));
        $this->setOrder($this->orderConfigurationProvider->getConfiguration($this->account));
        $this->setInstallments($this->installmentsConfigurationProvider->getConfiguration($this->account));
        $this->setState($this->stateConfigurationProvider->getConfiguration($this->account));
        $this->setPayment($this->paymentConfigurationProvider->getConfiguration($this->account, $fillPayments, $excludePayments));
        $this->setCredentials($this->credentialsConfigurationProvider->getConfiguration($this->account));
        $this->setApple($this->appleConfigurationProvider->getConfiguration($this->account, $fillCerts));
        $this->setGoogle($this->googleConfigurationProvider->getConfiguration($this->account));
        $this->setCards($this->cardsConfigurationProvider->getConfiguration($this->account));
        $this->setBlik($this->blikConfigurationProvider->getConfiguration($this->account));
    }

    public function getExtraCharge(): ?ExtraChargeConfig
    {
        return $this->extraCharge;
    }

    /**
     * @param ExtraChargeConfig $extraCharge
     *
     * @return Przelewy24Config
     */
    public function setExtraCharge(ExtraChargeConfig $extraCharge)
    {
        $this->extraCharge = $extraCharge;

        return $this;
    }

    public function getOrder(): ?OrderConfig
    {
        return $this->order;
    }

    /**
     * @param OrderConfig $order
     *
     * @return Przelewy24Config
     */
    public function setOrder(OrderConfig $order)
    {
        $this->order = $order;

        return $this;
    }

    public function getInstallments(): ?InstallmentsConfig
    {
        return $this->installments;
    }

    /**
     * @return Przelewy24Config
     */
    public function setInstallments(InstallmentsConfig $installments)
    {
        $this->installments = $installments;

        return $this;
    }

    public function getPayment(): ?PaymentConfig
    {
        return $this->payment;
    }

    /**
     * @param PaymentConfig $payment
     *
     * @return Przelewy24Config
     */
    public function setPayment(PaymentConfig $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    public function getState(): ?StateConfig
    {
        return $this->state;
    }

    /**
     * @param StateConfig $state
     *
     * @return Przelewy24Config
     */
    public function setState(StateConfig $state)
    {
        $this->state = $state;

        return $this;
    }

    public function getTime(): ?TimeConfig
    {
        return $this->time;
    }

    public function getApple(): ?AppleConfig
    {
        return $this->apple;
    }

    /**
     * @return Przelewy24Config
     */
    public function setApple(AppleConfig $apple)
    {
        $this->apple = $apple;

        return $this;
    }

    public function getCards(): ?CardsConfig
    {
        return $this->cards;
    }

    /**
     * @return Przelewy24Config
     */
    public function setCards(CardsConfig $cards)
    {
        $this->cards = $cards;

        return $this;
    }

    public function getGoogle(): ?GoogleConfig
    {
        return $this->google;
    }

    /**
     * @return Przelewy24Config
     */
    public function setGoogle(GoogleConfig $google)
    {
        $this->google = $google;

        return $this;
    }

    /**
     * @param TimeConfig $time
     *
     * @return Przelewy24Config
     */
    public function setTime(TimeConfig $time)
    {
        $this->time = $time;

        return $this;
    }

    public function getBlik(): ?BlikConfig
    {
        return $this->blik;
    }

    /**
     * @param BlikConfig $blik
     *
     * @return Przelewy24Config
     */
    public function setBlik($blik)
    {
        $this->blik = $blik;

        return $this;
    }

    public function getObjectByType($type)
    {
        switch ($type) {
            case FormTypeEnum::CREDENTIALS:
                return $this->credentials;
            case FormTypeEnum::STATE:
                return $this->state;
            case FormTypeEnum::ORDER:
                return $this->order;
            case FormTypeEnum::PAYMENT:
                return $this->payment;
            case FormTypeEnum::CARDS:
                return $this->cards;
            case FormTypeEnum::GOOGLE:
                return $this->google;
            case FormTypeEnum::APPLE:
                return $this->apple;
            case FormTypeEnum::EXTRA_CHARGE:
                return $this->extraCharge;
            case FormTypeEnum::TIME:
                return $this->time;
            case FormTypeEnum::INSTALLMENTS:
                return $this->installments;
            case FormTypeEnum::BLIK:
                return $this->blik;
        }
    }

    public function cloneObjectByType($type)
    {
        switch ($type) {
            case FormTypeEnum::CREDENTIALS:
                $this->credentials = clone $this->credentials;
                // no break
            case FormTypeEnum::STATE:
                $this->state = clone $this->state;
                // no break
            case FormTypeEnum::ORDER:
                $this->order = clone $this->order;
                // no break
            case FormTypeEnum::PAYMENT:
                $this->payment = clone $this->payment;
                // no break
            case FormTypeEnum::CARDS:
                $this->cards = clone $this->cards;
                // no break
            case FormTypeEnum::GOOGLE:
                $this->google = clone $this->google;
                // no break
            case FormTypeEnum::APPLE:
                $this->apple = clone $this->apple;
                // no break
            case FormTypeEnum::EXTRA_CHARGE:
                $this->extraCharge = clone $this->extraCharge;
                // no break
            case FormTypeEnum::TIME:
                $this->time = clone $this->time;
                // no break
            case FormTypeEnum::INSTALLMENTS:
                $this->installments = clone $this->installments;
                // no break
            case FormTypeEnum::BLIK:
                $this->blik = clone $this->blik;
        }
    }
}
