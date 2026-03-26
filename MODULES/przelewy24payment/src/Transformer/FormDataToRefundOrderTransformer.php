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

namespace Przelewy24\Transformer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Collection\RefundOrderProductCollection;
use Przelewy24\Dto\RefundOrder;
use Przelewy24\Dto\RefundOrderProduct;

class FormDataToRefundOrderTransformer
{
    public function transform(array $data)
    {
        $refundOrder = new RefundOrder();
        $refundOrder->setAmount($data['total_refund']);
        $refundOrder->setSessionId($data['session_id']);
        $refundOrder->setIdOrder($data['id_order']);
        $refundProductsCollection = new RefundOrderProductCollection();
        foreach ($data as $key => $value) {
            if (strpos($key, 'order_detail_') !== false) {
                $refundOrderProduct = new RefundOrderProduct();
                $exploded = explode('_', $key);
                if (count($exploded) == 3) {
                    $refundOrderProduct->setIdOrderDetail($exploded[2]);
                    $refundOrderProduct->setQuantity($value);
                    $refundProductsCollection->add($refundOrderProduct);
                }
            }
        }
        $refundOrder->setProducts($refundProductsCollection);

        return $refundOrder;
    }
}
