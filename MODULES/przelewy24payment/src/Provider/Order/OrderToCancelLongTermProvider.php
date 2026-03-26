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

namespace Przelewy24\Provider\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Order\Interfaces\OrderCancelProviderInterface;

class OrderToCancelLongTermProvider implements OrderCancelProviderInterface
{
    public function getOrdersToCancel(): array
    {
        $allTimeConfig = Przlewy24AccountModel::getAllTimeConfig();
        $orders = [];
        foreach ($allTimeConfig as $timeConfig) {
            $result = Przlewy24AccountModel::getOrdersToCancel(
                (int) $timeConfig['time_limit_long_term'],
                (int) $timeConfig['id_account'],
                [],
                [ModuleConfiguration::LONG_TERM_PAYMENT_ID]
            );
            $orders = array_unique(array_merge($orders, $result));
        }

        return $orders;
    }
}
