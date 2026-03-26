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

namespace Przelewy24\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Hook\Interfaces\HookInterface;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Calculator\CalculatorConfigurationProvider;
use Przelewy24\Provider\Configuration\ExtraChargeConfigurationProvider;

class DisplayExpressCheckout implements HookInterface
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var CalculatorConfigurationProvider
     */
    private $calculatorConfigurationProvider;
    /**
     * @var AmountExtraChargeCalculator
     */
    private $chargeCalculator;
    /**
     * @var ExtraChargeConfigurationProvider
     */
    private $extraChargeConfigurationProvider;

    public function __construct(
        \Context $context,
        CalculatorConfigurationProvider $calculatorConfigurationProvider,
        AmountExtraChargeCalculator $chargeCalculator,
        ExtraChargeConfigurationProvider $extraChargeConfigurationProvider
    ) {
        $this->context = $context;
        $this->calculatorConfigurationProvider = $calculatorConfigurationProvider;
        $this->chargeCalculator = $chargeCalculator;
        $this->extraChargeConfigurationProvider = $extraChargeConfigurationProvider;
    }

    public function execute($params)
    {
        $model = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop((int) $this->context->currency->id, (int) $this->context->cart->id_shop);
        $extraChargeConfig = $this->extraChargeConfigurationProvider->getConfiguration($model);
        $total = $this->context->cart->getOrderTotal() + $this->chargeCalculator->getAmount($extraChargeConfig, $this->context->cart);
        $config = $this->calculatorConfigurationProvider->getConfiguration($total, true);
        if (empty($config)) {
            return '';
        }

        $this->context->smarty->assign(['_p24ConfigCalculator' => json_encode($config)]);

        return $this->context->smarty->fetch('module:przelewy24payment/views/templates/hook/calculator_button.tpl');
    }
}
