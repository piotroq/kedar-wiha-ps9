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

namespace Przelewy24\Handler\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Przlewy24AccountModel;

class OrderCancelHandler
{
    private $providers;

    private $orders;

    public function __construct($providers)
    {
        $this->providers = $providers;
    }

    public function cancelOrders()
    {
        $this->_getOrders();
        $this->_cancelOrders();
    }

    private function _getOrders()
    {
        $this->orders = [];
        foreach ($this->providers as $provider) {
            $this->orders = array_unique(array_merge($provider->getOrdersToCancel(), $this->orders));
        }
    }

    private function _cancelOrders()
    {
        foreach ($this->orders as $idOrder) {
            $order = new \Order($idOrder);
            if (\Validate::isLoadedObject($order)) {
                $order->setCurrentState((int) \Configuration::get('PS_OS_ERROR'));
                Przlewy24AccountModel::addOrderCanceled($idOrder);
            }
        }
    }
}
