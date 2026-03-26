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

namespace Przelewy24\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Model\Dto\Przelewy24Config;

class OrderCreator
{
    private $chargeCalculator;
    private $module;

    public function __construct(
        AmountExtraChargeCalculator $chargeCalculator,
        \Module $module
    ) {
        $this->chargeCalculator = $chargeCalculator;
        $this->module = $module;
    }

    public function createOrder(\Cart $cart, \Customer $customer, Przelewy24Config $config)
    {
        if (!\Order::getIdByCartId($cart->id)) {
            $this->module->validateOrder(
                (int) $cart->id,
                (int) $config->getState()->getIdStateBeforePayment(),
                $cart->getOrderTotal(),
                $this->module->displayName,
                null,
                [],
                (int) $cart->id_currency,
                false,
                $customer->secure_key
            );
            $this->chargeCalculator->addAmountToOrder($config->getExtraCharge(), $cart);
        }
    }
}
