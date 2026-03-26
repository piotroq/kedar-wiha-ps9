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

namespace Przelewy24\Refund;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Refund;
use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRefund;
use Przelewy24\Api\Przelewy24\Request\RefundTransactionRequest;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Model\Przelewy24RefundModel;
use Przelewy24\Refund\Voter\RefundVoter;
use Przelewy24\Refund\Voter\ResponseTransactionRefundVoter;

class RefundTransaction
{
    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    private $transactionScope;

    private $model;

    private $transactionRefund;

    /**
     * @var ResponseTransactionRefundVoter
     */
    private $transactionRefundVoter;

    /**
     * @var RefundVoter
     */
    private $refundVoter;

    public function __construct(
        TransactionScopeFactory $transactionScopeFactory,
        ResponseTransactionRefundVoter $transactionRefundVoter,
        RefundVoter $refundVoter
    ) {
        $this->transactionScopeFactory = $transactionScopeFactory;
        $this->transactionRefundVoter = $transactionRefundVoter;
        $this->refundVoter = $refundVoter;
    }

    public function refund(TransactionRefund $transactionRefund)
    {
        /* @var Refund $refundObject */
        $this->transactionRefund = $transactionRefund;
        $this->refundVoter->vote($this->_getRefund());
        $this->_createTransactionScope();
        $this->_createModel();
        $result = $this->_sendRequest();
        $this->transactionRefundVoter->voteResult($result);
    }

    private function _getRefund()
    {
        return current($this->transactionRefund->getRefunds());
    }

    private function _createTransactionScope()
    {
        return $this->transactionScope = $this->transactionScopeFactory->factory($this->_getRefund()->getSessionId());
    }

    private function _createModel()
    {
        $refund = $this->_getRefund();
        $this->model = new Przelewy24RefundModel();
        $this->model->session_id = $refund->getSessionId();
        $this->model->amount = $refund->getAmount();
        $this->model->description = $refund->getDescription();
        $this->model->save();
        $this->transactionRefund->setRequestId($this->model->reference);
    }

    private function _sendRequest()
    {
        $request = new RefundTransactionRequest($this->transactionRefund);

        return $this->transactionScope->getConnection()->sendRequest($request);
    }
}
