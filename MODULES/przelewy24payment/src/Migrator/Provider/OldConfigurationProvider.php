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

namespace Przelewy24\Migrator\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Migrator\Collection\OldConfigurationCollection;
use Przelewy24\Migrator\Dto\Account;
use Przelewy24\Migrator\Dto\Credentials;
use Przelewy24\Migrator\Dto\ExtraCharge;
use Przelewy24\Migrator\Iterator\CurrencyIterator;
use Przelewy24\Migrator\Iterator\ShopIterator;

class OldConfigurationProvider
{
    private $collection;

    public function getOldConfigurations(): OldConfigurationCollection
    {
        $this->collection = new OldConfigurationCollection();
        $shopsIterator = new ShopIterator();
        $currenciesIterator = new CurrencyIterator();

        foreach ($shopsIterator->shops() as $shop) {
            foreach ($currenciesIterator->currencies() as $currency) {
                $this->_getConfiguration($shop, $currency);
            }
        }

        return $this->collection;
    }

    private function _getConfiguration($shop, $currency)
    {
        $idShop = $shop['id_shop'];
        $shopName = $shop['name'];
        $currencyCode = $currency['iso_code'];

        $suffix = $this->getSuffix($currencyCode);

        $posId = (int) $this->getConfigValue($idShop, 'P24_SHOP_ID', $suffix);
        $apiKey = (string) $this->getConfigValue($idShop, 'P24_API_KEY', $suffix);
        $salt = (string) $this->getConfigValue($idShop, 'P24_SALT', $suffix);
        $testMode = (bool) $this->getConfigValue($idShop, 'P24_TEST_MODE', $suffix);

        $chargePercent = (int) $this->getConfigValue($idShop, 'P24_EXTRA_CHARGE_PERCENT', $suffix);
        $chargeAmount = (float) $this->getConfigValue($idShop, 'P24_EXTRA_CHARGE_AMOUNT', $suffix);

        if (empty($idShop) || empty($currency['id_currency'])) {
            return;
        }
        $account = new Account((int) $idShop, (string) $shopName, (int) $currency['id_currency'], (string) $currency['iso_code'], (bool) $testMode);
        if (!empty($posId) && !empty($salt) && !empty($apiKey)) {
            $account->setCredentials(new Credentials((int) $posId, (int) $posId, (string) $salt, (string) $apiKey, (bool) $testMode));
        }
        if ($chargeAmount != false || $chargePercent != false) {
            $account->setExtraCharge(new ExtraCharge((float) $chargeAmount, (int) $chargePercent));
        }
        $this->collection->add($account);
    }

    private function getConfigValue($idShop, $key, $suffix)
    {
        return \Configuration::get($key . $suffix, null, null, $idShop);
    }

    private function getSuffix($currency)
    {
        if ('PLN' === $currency) {
            return '';
        } else {
            return '_' . $currency;
        }
    }
}
