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

namespace Przelewy24\Subscriber;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Event\SaveConfigEvent;
use Przelewy24\Model\Dto\AppleConfig;
use Przelewy24\Model\Dto\BlikConfig;
use Przelewy24\Model\Dto\CardsConfig;
use Przelewy24\Model\Dto\ExtraChargeConfig;
use Przelewy24\Model\Dto\GoogleConfig;
use Przelewy24\Model\Dto\InstallmentsConfig;
use Przelewy24\Model\Dto\OrderConfig;
use Przelewy24\Model\Dto\PaymentConfig;
use Przelewy24\Model\Dto\StateConfig;
use Przelewy24\Model\Dto\TimeConfig;
use Przelewy24\Model\PaymentConfigManager;
use Przelewy24\Model\Przlewy24AccountModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSaveEventSubscriber implements EventSubscriberInterface
{
    private $paymentConfigManager;

    public function __construct()
    {
        $this->paymentConfigManager = new PaymentConfigManager();
    }

    public static function getSubscribedEvents()
    {
        return [
            SaveConfigEvent::NAME => 'onConfigSave',
        ];
    }

    public function onConfigSave(SaveConfigEvent $event)
    {
        if ($event->getData() instanceof AppleConfig) {
            return $this->onConfigSaveApple($event, $event->getData());
        } elseif ($event->getData() instanceof CardsConfig) {
            return $this->onConfigSaveCards($event, $event->getData());
        } elseif ($event->getData() instanceof ExtraChargeConfig) {
            return $this->onConfigSaveExtraCharge($event, $event->getData());
        } elseif ($event->getData() instanceof GoogleConfig) {
            return $this->onConfigSaveGoogle($event, $event->getData());
        } elseif ($event->getData() instanceof InstallmentsConfig) {
            return $this->onConfigSaveInstallments($event, $event->getData());
        } elseif ($event->getData() instanceof OrderConfig) {
            return $this->onConfigSaveOrder($event, $event->getData());
        } elseif ($event->getData() instanceof PaymentConfig) {
            return $this->onConfigSavePayment($event, $event->getData());
        } elseif ($event->getData() instanceof StateConfig) {
            return $this->onConfigSaveState($event, $event->getData());
        } elseif ($event->getData() instanceof TimeConfig) {
            return $this->onConfigSaveTime($event, $event->getData());
        } elseif ($event->getData() instanceof BlikConfig) {
            return $this->onConfigSaveBlik($event, $event->getData());
        }
    }

    public function onConfigSaveApple(SaveConfigEvent $event, AppleConfig $data)
    {
        return $this->paymentConfigManager->manageOneClick(
            new Przlewy24AccountModel($data->getIdAccount()),
            $event->getConfig()->getPayment(),
            $data->getOneClick(),
            (bool) $event->getConfig()->getApple()->getOneClick(),
            PaymentTypeEnum::APPLE_PAYMENT
        );
    }

    public function onConfigSaveCards(SaveConfigEvent $event, CardsConfig $data)
    {
        return $this->paymentConfigManager->manageOneClick(
            new Przlewy24AccountModel($data->getIdAccount()),
            $event->getConfig()->getPayment(),
            $data->getPaymentInStore(),
            (bool) $event->getConfig()->getCards()->getPaymentInStore(),
            PaymentTypeEnum::CARD_PAYMENT
        );
    }

    public function onConfigSaveExtraCharge(SaveConfigEvent $event, ExtraChargeConfig $data)
    {
    }

    public function onConfigSaveGoogle(SaveConfigEvent $event, GoogleConfig $data)
    {
        return $this->paymentConfigManager->manageOneClick(
            new Przlewy24AccountModel($data->getIdAccount()),
            $event->getConfig()->getPayment(),
            $data->getOneClick(),
            (bool) $event->getConfig()->getGoogle()->getOneClick(),
            PaymentTypeEnum::GOOGLE_PAYMENT
        );
    }

    public function onConfigSaveInstallments(SaveConfigEvent $event, InstallmentsConfig $data)
    {
    }

    public function onConfigSaveOrder(SaveConfigEvent $event, OrderConfig $data)
    {
    }

    public function onConfigSavePayment(SaveConfigEvent $event, PaymentConfig $data)
    {
        $paymentMethodCollection = new PaymentMethodCollection();
        if ($event->getConfig()->getApple()->getOneClick()) {
            $this->paymentConfigManager->addTypeToCollection($paymentMethodCollection, PaymentTypeEnum::APPLE_PAYMENT, $data);
        }
        if ($event->getConfig()->getGoogle()->getOneClick()) {
            $this->paymentConfigManager->addTypeToCollection($paymentMethodCollection, PaymentTypeEnum::GOOGLE_PAYMENT, $data);
        }
        if ($event->getConfig()->getCards()->getOneClickCard()) {
            $this->paymentConfigManager->addTypeToCollection($paymentMethodCollection, PaymentTypeEnum::CARD_PAYMENT, $data);
        }
        if ($event->getConfig()->getBlik()->getBlikLevel0()) {
            $paymentMethod = new PaymentMethod();
            $paymentMethod->setId(ModuleConfiguration::BLIK_LEVEL_O_ID_PAYMENT);
            $paymentMethodCollection->add($paymentMethod);
        }
        $this->paymentConfigManager->addTypeToCollection($paymentMethodCollection, PaymentTypeEnum::INSTALLMENTS_PAYMENT, $data);
        $this->paymentConfigManager->addPaymentCollection($paymentMethodCollection, $data, new Przlewy24AccountModel($data->getIdAccount()));
    }

    public function onConfigSaveState(SaveConfigEvent $event, StateConfig $data)
    {
    }

    public function onConfigSaveTime(SaveConfigEvent $event, TimeConfig $data)
    {
    }

    public function onConfigSaveBlik(SaveConfigEvent $event, BlikConfig $data)
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setId(ModuleConfiguration::BLIK_LEVEL_O_ID_PAYMENT);
        if ($data->getBlikLevel0()) {
            $this->paymentConfigManager->addPayment($paymentMethod, new Przlewy24AccountModel($data->getIdAccount()), $event->getConfig()->getPayment());
        } else {
            $this->paymentConfigManager->removePayment($paymentMethod, new Przlewy24AccountModel($data->getIdAccount()), $event->getConfig()->getPayment());
        }
    }
}
