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

use Przelewy24\Hook\Interfaces\HookInterface;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Calculator\CalculatorConfigurationProvider;

class DisplayProductAdditionalInfo implements HookInterface
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var CalculatorConfigurationProvider
     */
    private $calculatorConfigurationProvider;
    private $config;

    public function __construct(
        \Context $context,
        CalculatorConfigurationProvider $calculatorConfigurationProvider,
        Przelewy24Config $config
    ) {
        $this->context = $context;
        $this->calculatorConfigurationProvider = $calculatorConfigurationProvider;
        $this->config = $config;
    }

    public function execute($params)
    {
        $account = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($this->context->currency->id, $this->context->shop->id);
        if (!$account) {
            return '';
        }
        $this->config->setAccount($account, false);
        if (!$this->config->getInstallments()->isEnableCalculatorOnProduct()) {
            return '';
        }

        $config = $this->calculatorConfigurationProvider->getConfiguration($params['product']['price_amount'], true);

        if (empty($config)) {
            return '';
        }

        $this->context->smarty->assign(['_p24ConfigCalculator' => json_encode($config)]);

        return $this->context->smarty->fetch('module:przelewy24payment/views/templates/hook/calculator_button.tpl');
    }
}
