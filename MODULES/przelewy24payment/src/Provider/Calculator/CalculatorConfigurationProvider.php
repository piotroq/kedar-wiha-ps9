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

namespace Przelewy24\Provider\Calculator;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;

class CalculatorConfigurationProvider
{
    private const PAYMENT_METHOD = '303';
    private const CURRENCY = 'PLN';
    private const LANG = 'pl';

    /**
     * @var \Context
     */
    private $context;

    /**
     * @var Przelewy24Config
     */
    private $config;

    public function __construct(\Context $context, Przelewy24Config $config)
    {
        $this->context = $context;
        $this->config = $config;
    }

    public function getConfiguration(float $total, $checkRangePrice = false): array
    {
        $model = $this->getAccountModel($this->getCurrency());
        if (!$model) {
            return [];
        }

        if ($this->getCurrency()->iso_code != ModuleConfiguration::CALCULATOR_CURRENCY) {
            return [];
        }

        $credentials = $this->initializeConfiguration($model);
        if (!$credentials) {
            return [];
        }

        if ($checkRangePrice && !$this->rangePriceIsValid($total)) {
            return [];
        }

        return $this->buildCalculatorConfig($total, $credentials);
    }

    private function rangePriceIsValid(float $total)
    {
        $min = ModuleConfiguration::MIN_PRODUCT_CALCULATOR_PRICE;
        $max = ModuleConfiguration::MAX_PRODUCT_CALCULATOR_PRICE;

        return $min <= $total && ($total <= $max);
    }

    private function getCurrency(\Cart $cart = null)
    {
        if (isset($cart->id_currency)) {
            return new \Currency($cart->id_currency);
        }
        if (isset($this->context->currency)) {
            return $this->context->currency;
        }
    }

    private function getAccountModel(\Currency $currency): ?Przlewy24AccountModel
    {
        $model = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop(
            (int) $currency->id,
            (int) $this->context->shop->id
        );

        if (!\Validate::isLoadedObject($model)) {
            return null;
        }

        return $model;
    }

    private function initializeConfiguration(Przlewy24AccountModel $model)
    {
        $this->config->setAccount($model, false);

        return $this->config->getCredentials();
    }

    private function calculateTotal(float $total): int
    {
        return (int) round($total * 100);
    }

    private function buildCalculatorConfig(float $total, $credentials): array
    {
        $posId = $credentials->getShopId();

        return [
            'sign' => $this->calculateSign($credentials->getSalt(), $posId, self::PAYMENT_METHOD),
            'posid' => $posId,
            'method' => self::PAYMENT_METHOD,
            'amount' => $this->calculateTotal($total),
            'currency' => self::CURRENCY,
            'lang' => self::LANG,
            'cms' => ModuleConfiguration::CALCULATOR_CMS,
            'test' => (bool) $this->config->getAccount()->test_mode,
        ];
    }

    private function calculateSign($crc, $posId, $method)
    {
        $params = [
            'crc' => (string) $crc,
            'posId' => (int) $posId,
            'method' => (int) $method,
        ];

        $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha384', $jsonData);
    }
}
