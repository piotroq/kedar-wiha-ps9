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

namespace Przelewy24\Manager\PaymentOptions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\AccountNotFoundApiException;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Factory\PaymentMethod\PaymentMethodCollectionFactory;
use Przelewy24\Helper\Image\ImageHelper;
use Przelewy24\Helper\Price\PriceHelper;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Manager\PaymentOptions\Config\ConfigPaymentOptions;
use Przelewy24\Manager\PaymentOptions\Providers\MainPaymentOptionProvider;
use Przelewy24\Manager\PaymentOptions\Providers\SeparatedPaymentOptionsProvider;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Translator\Adapter\Translator;

class PaymentOptionsManager
{
    /**
     * @var \Context
     */
    private $context;

    private $model;

    /**
     * @var PaymentMethodCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PaymentMethodCollection
     */
    private $paymentMethodsCollection;

    private $paymentOptions = [];

    /**
     * @var AmountExtraChargeCalculator
     */
    private $chargeCalculator;

    /**
     * @var Przelewy24Config
     */
    private $config;

    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var \Currency
     */
    private $currency;

    /**
     * @var string
     */
    private $extraCharge;

    /**
     * @var float|int
     */
    private $cartTotal;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var MainPaymentOptionProvider
     */
    private $mainPaymentOptionProvider;

    /**
     * @var SeparatedPaymentOptionsProvider
     */
    private $separatedPaymentOptionsProvider;

    /**
     * @var ConfigPaymentOptions
     */
    private $configPaymentOptions;
    /**
     * @var PriceHelper
     */
    private $priceHelper;
    private $urlHelper;

    public function __construct(
        \Context $context,
        PaymentMethodCollectionFactory $collectionFactory,
        AmountExtraChargeCalculator $chargeCalculator,
        Przelewy24Config $config,
        ImageHelper $imageHelper,
        Translator $translator,
        ConnectionFactory $connectionFactory,
        MainPaymentOptionProvider $mainPaymentOptionProvider,
        SeparatedPaymentOptionsProvider $separatedPaymentOptionsProvider,
        PriceHelper $priceHelper,
        UrlHelper $urlHelper
    ) {
        $this->context = $context;
        $this->collectionFactory = $collectionFactory;
        $this->paymentMethodsCollection = new PaymentMethodCollection();
        $this->chargeCalculator = $chargeCalculator;
        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->translator = $translator;
        $this->connectionFactory = $connectionFactory;
        $this->mainPaymentOptionProvider = $mainPaymentOptionProvider;
        $this->separatedPaymentOptionsProvider = $separatedPaymentOptionsProvider;
        $this->priceHelper = $priceHelper;
        $this->urlHelper = $urlHelper;
    }

    public function getPaymentOptions(\Cart $cart)
    {
        try {
            $this->_init($cart);
            $this->_smartyAssign();
            $this->_getPaymentOptions();
        } catch (\Exception $exception) {
            return [];
        }

        return $this->paymentOptions;
    }

    private function _init(\Cart $cart)
    {
        $this->cart = $cart;
        $this->currency = new \Currency($this->cart->id_currency);
        $this->model = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop((int) $this->currency->id, (int) $this->cart->id_shop);
        $this->connectionFactory->factory($this->model);
        $this->config->setAccount($this->model, false);
        $this->extraCharge = $this->chargeCalculator->getAmount($this->config->getExtraCharge(), $this->cart);
        $this->cartTotal = $this->cart->getOrderTotal();

        if (!\Validate::isLoadedObject($this->model)) {
            throw new AccountNotFoundApiException('Account not found', $this->translator->trans('Account not found', [], 'Modules.Przelewy24payment.Exception'));
        }

        $this->paymentMethodsCollection = $this->collectionFactory->factory(
            [
                'account' => $this->model,
                'currency' => $this->currency->iso_code,
                'amount' => (int) round(($this->cartTotal + $this->extraCharge) * 100),
            ]);
        $this->configPaymentOptions = new ConfigPaymentOptions();
        $this->configPaymentOptions->setCart($this->cart);
        $this->configPaymentOptions->setCurrency($this->currency);
        $this->configPaymentOptions->setModel($this->model);
        $this->configPaymentOptions->setPaymentMethodsCollection($this->paymentMethodsCollection);
        $this->configPaymentOptions->setConfig($this->config);
    }

    private function _getPaymentOptions()
    {
        $this->paymentOptions = $this->separatedPaymentOptionsProvider->getPaymentOptions($this->configPaymentOptions);
        $this->paymentOptions[] = $this->mainPaymentOptionProvider->getPaymentOptions($this->configPaymentOptions);
    }

    private function _smartyAssign()
    {
        $this->context->smarty->assign(
            [
                'przelewy_logo' => $this->imageHelper->createUrl('przelewy24-logo.svg'),
                'intro' => $this->config->getOrder()->getIntroText(),
                'extra_charge' => $this->priceHelper->displayPrice($this->extraCharge, $this->currency),
                'extra_charge_value' => $this->extraCharge,
                'total' => $this->priceHelper->displayPrice($this->cartTotal + (float) $this->extraCharge, $this->currency),
                'total_amount' => number_format($this->cartTotal + (float) $this->extraCharge, 2),
                'currency_iso' => $this->currency->iso_code,
                'regulations_link' => $this->urlHelper->getRegulationsUrl($this->context->language->iso_code),
                'information_link' => $this->urlHelper->getInformationGdprUrl($this->context->language->iso_code),
            ]);
    }
}
