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

namespace Przelewy24\Manager\PaymentOptions\Providers;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Manager\PaymentOptions\Config\ConfigPaymentOptions;
use Przelewy24\Manager\PaymentOptions\Presenter\ApplePayPaymentOptionPresenter;
use Przelewy24\Manager\PaymentOptions\Presenter\Blik0PaymentOptionPresenter;
use Przelewy24\Manager\PaymentOptions\Presenter\CardPaymentOptionPresenter;
use Przelewy24\Manager\PaymentOptions\Presenter\GooglePayPaymentOptionPresenter;
use Przelewy24\Manager\PaymentOptions\Presenter\PaymentOptionPresenter;

class SeparatedPaymentOptionsProvider
{
    /**
     * @var CardPaymentOptionPresenter
     */
    private $cardPaymentOptionPresenter;

    /**
     * @var PaymentOptionPresenter
     */
    private $paymentOptionPresenter;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var GooglePayPaymentOptionPresenter
     */
    private $googlePayPaymentOptionPresenter;

    /**
     * @var ApplePayPaymentOptionPresenter
     */
    private $applePayPaymentOptionPresenter;
    private $blik0PaymentOptionPresenter;

    public function __construct(
        ApplePayPaymentOptionPresenter $applePayPaymentOptionPresenter,
        GooglePayPaymentOptionPresenter $googlePayPaymentOptionPresenter,
        CardPaymentOptionPresenter $cardPaymentOptionPresenter,
        Blik0PaymentOptionPresenter $blik0PaymentOptionPresenter,
        PaymentOptionPresenter $paymentOptionPresenter,
        UrlHelper $urlHelper
    ) {
        $this->cardPaymentOptionPresenter = $cardPaymentOptionPresenter;
        $this->paymentOptionPresenter = $paymentOptionPresenter;
        $this->urlHelper = $urlHelper;
        $this->googlePayPaymentOptionPresenter = $googlePayPaymentOptionPresenter;
        $this->applePayPaymentOptionPresenter = $applePayPaymentOptionPresenter;
        $this->blik0PaymentOptionPresenter = $blik0PaymentOptionPresenter;
    }

    public function getPaymentOptions(ConfigPaymentOptions $configPaymentOptions)
    {
        $paymentOptions = [];
        if ($configPaymentOptions->getConfig()->getPayment()->getPaymentMethodSeparate()) {
            $idsSeparatePaymentMethods = $configPaymentOptions->getModel()->getPaymentMethodSeparate();
            $separateMethodsCollection = $configPaymentOptions->getPaymentMethodsCollection()->intersectByIds($idsSeparatePaymentMethods);
            foreach ($separateMethodsCollection as $separateMethod) {
                $separateMethod->setFrontUrl($this->urlHelper->getUrlFrontPayment($separateMethod->getId(), $configPaymentOptions->getCart()->id));
                $paymentOptions[] = $this->_presentPayment($configPaymentOptions, $separateMethod);
            }
        }

        return $paymentOptions;
    }

    private function _presentPayment(ConfigPaymentOptions $configPaymentOptions, PaymentMethod $paymentMethod)
    {
        switch ($paymentMethod->getType()) {
            case PaymentTypeEnum::CARD_PAYMENT:
                return $configPaymentOptions->getConfig()->getCards()->getPaymentInStore()
                    ? $this->cardPaymentOptionPresenter->present($configPaymentOptions, $paymentMethod)
                    : $this->paymentOptionPresenter->present($configPaymentOptions, $paymentMethod);
            case PaymentTypeEnum::GOOGLE_PAYMENT:
                return $configPaymentOptions->getConfig()->getGoogle()->getOneClick()
                    ? $this->googlePayPaymentOptionPresenter->present($configPaymentOptions, $paymentMethod)
                    : $this->paymentOptionPresenter->present($configPaymentOptions, $paymentMethod);
            case PaymentTypeEnum::APPLE_PAYMENT:
                return $configPaymentOptions->getConfig()->getApple()->getOneClick()
                    ? $this->applePayPaymentOptionPresenter->present($configPaymentOptions, $paymentMethod)
                    : $this->paymentOptionPresenter->present($configPaymentOptions, $paymentMethod);
            case PaymentTypeEnum::BLIK_LEVEL_O_PAYMENT:
                return $this->blik0PaymentOptionPresenter->present($configPaymentOptions, $paymentMethod);
            default:
                return $this->paymentOptionPresenter->present($configPaymentOptions, $paymentMethod);
        }
    }
}
