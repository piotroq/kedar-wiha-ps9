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

namespace Przelewy24\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransactionHistoryRefund
{
    private $idRefund;

    private $refundDate;

    private $refundAmount;

    private $reference;

    private $received;

    /**
     * @return mixed
     */
    public function getIdRefund()
    {
        return $this->idRefund;
    }

    /**
     * @param mixed $idRefund
     *
     * @return TransactionHistoryRefund
     */
    public function setIdRefund($idRefund)
    {
        $this->idRefund = $idRefund;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefundAmount()
    {
        return $this->refundAmount;
    }

    /**
     * @param mixed $refundAmount
     *
     * @return TransactionHistoryRefund
     */
    public function setRefundAmount($refundAmount)
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefundDate()
    {
        return $this->refundDate;
    }

    /**
     * @param mixed $refundDate
     *
     * @return TransactionHistoryRefund
     */
    public function setRefundDate($refundDate)
    {
        $this->refundDate = $refundDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * @param mixed $received
     *
     * @return TransactionHistoryRefund
     */
    public function setReceived($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     *
     * @return TransactionHistoryRefund
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }
}
