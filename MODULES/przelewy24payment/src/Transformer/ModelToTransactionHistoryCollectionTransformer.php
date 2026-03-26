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

use Przelewy24\Collection\TransactionHistoryCollection;
use Przelewy24\Collection\TransactionHistoryRefundCollection;
use Przelewy24\Dto\TransactionHistory;
use Przelewy24\Dto\TransactionHistoryRefund;
use Przelewy24\Helper\Price\PriceHelper;
use Przelewy24\Helper\Url\UrlHelper;

class ModelToTransactionHistoryCollectionTransformer
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;
    private $priceHelper;

    public function __construct(UrlHelper $urlHelper, PriceHelper $priceHelper)
    {
        $this->urlHelper = $urlHelper;
        $this->priceHelper = $priceHelper;
    }

    public function transform($data)
    {
        $collection = new TransactionHistoryCollection();
        foreach ($data as $row) {
            if (!isset($idCurrency)) {
                $order = new \Order($row['ps_id_order']);
                $idCurrency = $order->id_currency;
            }
            $transactionHistory = new TransactionHistory();
            $transactionHistory->setSessionId($row['session_id']);
            $transactionHistory->setIdOrder($row['p24_id_order']);
            $transactionHistory->setPayedAmount($this->priceHelper->displayPrice($row['payed_amount'] / 100, (int) $idCurrency));
            $transactionHistory->setReceived($row['received']);
            $transactionHistory->setDateAdd($row['date_add']);
            if (!empty($row['received']) && !empty($row['p24_id_order'])) {
                $transactionHistory->setLink($this->urlHelper->getUrlPanel((bool) $row['test_mode'], $row['p24_id_order']));
            }
            $collectionRefund = new TransactionHistoryRefundCollection();
            if (isset($row['refunds'])) {
                foreach ($row['refunds'] as $id_refund => $refund) {
                    $transactionHistoryRefund = new TransactionHistoryRefund();
                    $transactionHistoryRefund->setIdRefund($id_refund);
                    $transactionHistoryRefund->setReference($refund['reference']);
                    $transactionHistoryRefund->setReceived($refund['refund_received']);
                    $transactionHistoryRefund->setRefundDate($refund['refund_date']);
                    $transactionHistoryRefund->setRefundAmount($this->priceHelper->displayPrice($refund['refund_amount'] / 100, (int) $idCurrency));
                    $collectionRefund->add($transactionHistoryRefund);
                }
            }
            $transactionHistory->setRefunds($collectionRefund);
            $collection->add($transactionHistory);
        }

        return $collection;
    }
}
