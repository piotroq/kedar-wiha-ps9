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

namespace Przelewy24\Resolver\Media\Driver;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Dto\Javascript\Config;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Resolver\Media\Driver\Interfaces\ControllerSetMediaDriverInterface;

class RepaymentSetMediaDriver implements ControllerSetMediaDriverInterface
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \Module
     */
    private $module;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var AmountExtraChargeCalculator
     */
    private $chargeCalculator;

    public function __construct(\Context $context, \Module $module, UrlHelper $urlHelper, AmountExtraChargeCalculator $chargeCalculator)
    {
        $this->context = $context;
        $this->module = $module;
        $this->urlHelper = $urlHelper;
        $this->chargeCalculator = $chargeCalculator;
    }

    public function getControllers(): array
    {
        return [\Przelewy24paymentrepaymentModuleFrontController::class];
    }

    public function registerMedia(Config $config, Przelewy24Config $accountConfig)
    {
        $this->context->controller->registerStylesheet(
            $this->module->name . '-front',
            'modules/' . $this->module->name . '/views/css/front/' . ModuleConfiguration::PRZELEWY24 . '.css',
            [
                'media' => 'all',
            ]
        );

        $this->context->controller->registerJavascript(
            $this->module->name . '-repayment',
            'modules/' . $this->module->name . '/views/js/front/' . ModuleConfiguration::PRZELEWY24 . '-repayment.js',
            [
                'position' => 'bottom',
            ]
        );

        \Media::addJsDef(['_p24ConfigTokenization' => $config]);
    }

    public function modifyConfig(Config $config, Przelewy24Config $accountConfig)
    {
        $config->getOptions()->size = ['height' => '330px'];
        $config->getOptions()->agreement = [
            'contentEnabled' => [
                'enabled' => false,
                'checkboxEnabled' => false,
            ],
        ];

        $token = \Tools::getValue('token');
        $idCart = Przlewy24AccountModel::getIdCartByToken($token);
        $cart = new \Cart((int) $idCart);

        $cart = \Validate::isLoadedObject($cart) ? $cart : $this->context->cart;
        $currency = new \Currency((int) $cart->id_currency);

        $config->getOptions()->paymentDetails = [
            'total' => $cart->getOrderTotal() + $this->chargeCalculator->getAmount($accountConfig->getExtraCharge(), $cart),
            'currency' => $currency->iso_code,
        ];

        if (
            $accountConfig->getGoogle()->getOneClick()
            && $accountConfig->getGoogle()->getIdMerchant()
            && $accountConfig->getGoogle()->getMerchantName()
        ) {
            $config->getOptions()->google = [
                'googleMerchantId' => $accountConfig->getGoogle()->getIdMerchant(),
                'googleMerchantName' => $accountConfig->getGoogle()->getMerchantName(),
            ];
        }

        if (
            $accountConfig->getApple()->getOneClick()
            && $accountConfig->getApple()->getIdMerchant()
            && $accountConfig->getApple()->getMerchantName()
            && $accountConfig->getApple()->getCert()
            && $accountConfig->getApple()->getPrivateKey()
        ) {
            $config->getOptions()->apple = [
                'appleMerchantId' => $accountConfig->getApple()->getIdMerchant(),
                'appleMerchantName' => $accountConfig->getApple()->getMerchantName(),
                'appleValidateUrl' => $this->urlHelper->getValidateMerchantUrl(),
                'appleCountryCode' => $this->context->country->iso_code ?? 'PL',
                'appleLocale' => $this->context->language->locale ?? 'pl-PL',
            ];
        }
    }
}
