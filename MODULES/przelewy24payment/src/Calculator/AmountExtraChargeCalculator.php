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

namespace Przelewy24\Calculator;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Exceptions\OrderNotExistException;
use Przelewy24\Model\Dto\ExtraChargeConfig;
use Przelewy24\Translator\Adapter\Translator;

class AmountExtraChargeCalculator
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getAmount(ExtraChargeConfig $config, \Cart $cart)
    {
        $total = $cart->getOrderTotal();
        $extraCharge = 0;
        if ($config->getExtraChargeAmount()) {
            $extraCharge = $config->getExtraChargeAmount();
        }
        $amountPercent = 0;
        if ($config->getExtraChargePercent()) {
            $amountPercent = round(($total * ((100 + $config->getExtraChargePercent()) / 100)) - $total, 2);
        }

        if ($amountPercent > $extraCharge) {
            $extraCharge = round($amountPercent, 2);
        }

        return (float) number_format($extraCharge, 2);
    }

    public function addAmountToOrder(ExtraChargeConfig $config, \Cart $cart)
    {
        $idOrder = \Order::getIdByCartId($cart->id);
        $order = new \Order($idOrder);
        if (!\Validate::isLoadedObject($order)) {
            throw new OrderNotExistException('Order not Exist', $this->translator->trans('Order not Exist', [], 'Modules.Przelewy24payment.Exception'));
        }

        $amount = $this->getAmount($config, $cart);

        $order->total_paid += $amount;
        $order->total_paid_tax_excl += $amount;
        $order->total_paid_tax_incl += $amount;
        $order->save();
    }
}
