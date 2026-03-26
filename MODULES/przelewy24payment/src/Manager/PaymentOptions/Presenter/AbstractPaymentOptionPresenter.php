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

namespace Przelewy24\Manager\PaymentOptions\Presenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Przelewy24\Cookie\LastPaymentMethod\LastPaymentMethodCookie;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Helper\Image\ImageHelper;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Manager\PaymentOptions\Config\ConfigPaymentOptions;
use Przelewy24\Resolver\Cards\CardsLogoResolver;
use Przelewy24\Translator\Adapter\Translator;

abstract class AbstractPaymentOptionPresenter
{
    /**
     * @var CardsLogoResolver
     */
    protected $cardsLogoResolver;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var LastPaymentMethodCookie
     */
    protected $lastPaymentMethodCookie;

    protected $translator;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function __construct(
        \Context $context,
        UrlHelper $urlHelper,
        LastPaymentMethodCookie $lastPaymentMethodCookie,
        ImageHelper $imageHelper,
        Translator $translator,
        CardsLogoResolver $cardsLogoResolver
    ) {
        $this->cardsLogoResolver = $cardsLogoResolver;
        $this->context = $context;
        $this->imageHelper = $imageHelper;
        $this->lastPaymentMethodCookie = $lastPaymentMethodCookie;
        $this->translator = $translator;
        $this->urlHelper = $urlHelper;
    }

    protected $configPaymentOptions;

    public function present(ConfigPaymentOptions $configPaymentOptions, PaymentMethod $paymentMethod = null)
    {
        $this->configPaymentOptions = $configPaymentOptions;
        $paymentOption = $this->_createPaymentOption($paymentMethod);
        $this->_createAdditionalInformation($paymentOption, $paymentMethod);
        $this->_createForm($paymentOption, $paymentMethod);

        return $paymentOption;
    }

    abstract protected function _createPaymentOption(?PaymentMethod $paymentMethod): PaymentOption;

    abstract protected function _createAdditionalInformation(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod);

    abstract protected function _createForm(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod);
}
