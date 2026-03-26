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
if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Apple\Request\ValidateMerchantRequest;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\AppleConfigurationProvider;

class Przelewy24paymentappleModuleFrontController extends ModuleFrontController
{
    public function displayAjaxValidateMerchant()
    {
        try {
            $cart = $this->context->cart;
            $request = $this->getContainer()->get(ValidateMerchantRequest::class);
            $appleConfiguiration = $this->getContainer()->get(AppleConfigurationProvider::class);
            $model = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($cart->id_currency, $cart->id_shop);
            $result = $request->sendRequest($appleConfiguiration->getConfiguration($model, true));
        } catch (Exception $e) {
            return $this->_returnResponseAjax(json_encode(['status' => 'error']));
        }

        return $this->_returnResponseAjax($result);
    }

    private function _returnResponseAjax($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo $params;
        exit;
    }
}
