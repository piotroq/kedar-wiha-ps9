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

use Przelewy24\Dto\RefundOrder;
use Przelewy24\Dto\RefundOrderProduct;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Provider\Refund\RefundProvider;
use Przelewy24\Refund\RefundTransaction;

class OrderRefundHandler
{
    /**
     * @var RefundProvider
     */
    private $refundProvider;

    /**
     * @var RefundOrder
     */
    private $refundOrder;

    /**
     * @var RefundTransaction
     */
    private $refundTransaction;

    /**
     * @var \Order
     */
    private $order;

    /**
     * @var array|bool|\mysqli_result|\PDOStatement|resource|null
     */
    private $productsDetail;

    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    /**
     * @var mixed
     */
    private $transactionScope;

    public function __construct(
        RefundProvider $refundProvider,
        RefundTransaction $refundTransaction,
        TransactionScopeFactory $transactionScopeFactory
    ) {
        $this->refundProvider = $refundProvider;
        $this->refundTransaction = $refundTransaction;
        $this->transactionScopeFactory = $transactionScopeFactory;
    }

    public function refund(RefundOrder $refundOrder)
    {
        $this->refundOrder = $refundOrder;
        $this->transactionScope = $this->transactionScopeFactory->factory($this->refundOrder->getSessionId());
        $this->order = new \Order($this->refundOrder->getIdOrder());
        $this->productsDetail = $this->order->getProductsDetail();
        $this->_refundPrzelewy24();
        $this->_addOrderSlip();
        $this->_updateStock();

        return $this->_updateOrderDetail();
    }

    private function _refundPrzelewy24()
    {
        $refundTransaction = $this->refundProvider->getTransactionRefund($this->refundOrder->getSessionId(), (int) round($this->refundOrder->getAmount() * 100), 'Refund Order');
        $this->refundTransaction->refund($refundTransaction);
    }

    private function _addOrderSlip()
    {
        if (!$this->transactionScope->getConfig()->getOrder()->getAlterStock()) {
            return true;
        }

        /* @var RefundOrderProduct $product */
        foreach ($this->refundOrder->getProducts() as $product) {
            if (!$product->getQuantity()) {
                continue;
            }
            foreach ($this->productsDetail as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $product->getIdOrderDetail()) {
                    $quantity = min((int) $product->getQuantity(), (int) $orderProduct['product_quantity']);
                    $orderProduct['quantity'] = $quantity;
                    $orderProduct['unit_price'] = $orderProduct['unit_price_tax_excl'];
                    $productsToPass[] = $orderProduct;
                    break;
                }
            }
        }
        if (!empty($productsToPass)) {
            return \OrderSlip::create($this->order, $productsToPass);
        }

        return true;
    }

    private function _updateStock()
    {
        foreach ($this->refundOrder->getProducts() as $product) {
            if (!$product->getQuantity()) {
                continue;
            }
            foreach ($this->productsDetail as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $product->getIdOrderDetail()) {
                    $stock = new \StockAvailable();
                    $stock->updateQuantity(
                        $orderProduct['product_id'],
                        $orderProduct['product_attribute_id'],
                        $product->getQuantity()
                    );

                    break;
                }
            }
        }

        return $this->order->update();
    }

    private function _updateOrderDetail()
    {
        $success = true;

        /* @var RefundOrderProduct $product */
        foreach ($this->refundOrder->getProducts() as $product) {
            if (!$product->getQuantity()) {
                continue;
            }
            $productFound = false;
            foreach ($this->productsDetail as $orderProduct) {
                if ((int) $orderProduct['id_order_detail'] === (int) $product->getIdOrderDetail()) {
                    $quantity = min((int) $product->getQuantity(), (int) $orderProduct['product_quantity']);
                    if ($quantity) {
                        $this->_updateOneLineOfOrderDetails($product);
                    }
                    $productFound = true;
                    break;
                }
            }
            if (!$productFound) {
                $success = false;
            }
        }

        $this->order->update();

        return $success;
    }

    private function _updateOneLineOfOrderDetails(RefundOrderProduct $product)
    {
        $orderDetail = new \OrderDetail($product->getIdOrderDetail());

        if (!property_exists($orderDetail, 'total_refunded_tax_incl')) {
            return;
        }
        if (!property_exists($orderDetail, 'total_refunded_tax_excl')) {
            return;
        }

        $withTaxUnit = round($orderDetail->unit_price_tax_incl, 2);
        $withoutTaxUnit = round($orderDetail->unit_price_tax_excl, 2);
        $withTax = round($withTaxUnit * $product->getQuantity(), 2);
        $withoutTax = round($withoutTaxUnit * $product->getQuantity(), 2);
        $withTaxRefunded = round($orderDetail->total_refunded_tax_incl, 2);
        $withoutTaxRefunded = round($orderDetail->total_refunded_tax_excl, 2);
        $withTaxRefunded = round($withTaxRefunded + $withTax, 2);
        $withoutTaxRefunded = round($withoutTaxRefunded + $withoutTax, 2);
        $orderDetail->total_refunded_tax_incl = $withTaxRefunded;
        $orderDetail->total_refunded_tax_excl = $withoutTaxRefunded;
        $orderDetail->update();
    }
}
