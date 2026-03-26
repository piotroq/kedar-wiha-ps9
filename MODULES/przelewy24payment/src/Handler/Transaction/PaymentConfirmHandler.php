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

namespace Przelewy24\Handler\Transaction;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Dto\Interfaces\TransactionVerifyRequiredDataInterface;
use Przelewy24\Dto\TransactionScope;
use Przelewy24\Exceptions\TransactionFullRefundedException;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Order\OrderCreator;
use Przelewy24\Payment\Voter\PrestashopOrderExistVoter;
use Przelewy24\Payment\Voter\VerifyTransactionVoter;
use Przelewy24\Provider\Refund\RefundProvider;
use Przelewy24\Refund\RefundTransaction;

class PaymentConfirmHandler
{
    /**
     * @var TransactionVerifyHandler
     */
    private $transactionVerifyHandler;

    /**
     * @var TransactionVerifyRequiredDataInterface
     */
    private $transactionData;

    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    /**
     * @var TransactionScope
     */
    private $transactionScope;

    /**
     * @var VerifyTransactionVoter
     */
    private $verifyTransactionVoter;

    /**
     * @var RefundProvider
     */
    private $refundProvider;

    /**
     * @var RefundTransaction
     */
    private $refundTransaction;

    /**
     * @var OrderCreator
     */
    private $orderCreator;

    /**
     * @var PrestashopOrderExistVoter
     */
    private $prestashopOrderExistVoter;

    public const CONFIRM = 'confirm';

    public const ALREADY_PAYED = 'already_payed';

    public const ORDER_CANCELLED = 'order_cancelled';
    public const ORDER_NOT_EXIST = 'order_not_exist';

    public function __construct(
        TransactionVerifyHandler $transactionVerifyHandler,
        TransactionScopeFactory $transactionScopeFactory,
        VerifyTransactionVoter $verifyTransactionVoter,
        RefundProvider $refundProvider,
        RefundTransaction $refundTransaction,
        OrderCreator $orderCreator,
        PrestashopOrderExistVoter $prestashopOrderExistVoter
    ) {
        $this->transactionVerifyHandler = $transactionVerifyHandler;
        $this->transactionScopeFactory = $transactionScopeFactory;
        $this->verifyTransactionVoter = $verifyTransactionVoter;
        $this->refundProvider = $refundProvider;
        $this->refundTransaction = $refundTransaction;
        $this->orderCreator = $orderCreator;
        $this->prestashopOrderExistVoter = $prestashopOrderExistVoter;
    }

    public function handle(TransactionVerifyRequiredDataInterface $transactionData)
    {
        $this->transactionData = $transactionData;
        $this->transactionScope = $this->transactionScopeFactory->factory($transactionData->getSessionId());
        $this->transactionScope->getTransaction()->setP24IdOrder($this->transactionData->getOrderId());

        if (!$this->prestashopOrderExistVoter->voteOrderExist($this->transactionScope)) {
            $this->_createOrder();
        }
        if (!$this->prestashopOrderExistVoter->voteOrderExist($this->transactionScope)) {
            return self::ORDER_NOT_EXIST;
        }
        if ($this->verifyTransactionVoter->votePayed($this->transactionScope)) {
            $this->_refund();

            return self::ALREADY_PAYED;
        }
        if ($this->verifyTransactionVoter->voteCanceled($this->transactionScope)) {
            $this->_refund();

            return self::ORDER_CANCELLED;
        }
        $this->_confirm();

        return self::CONFIRM;
    }

    private function _confirm()
    {
        $this->_verifyTransaction();
        $this->_markAsReceived();
        $this->_changeStatus();
        $this->_addPaymentToOrder();

        return true;
    }

    private function _refund()
    {
        $this->_verifyTransaction();
        try {
            $this->refundTransaction->refund($this->_createTransactionRefund());
        } catch (TransactionFullRefundedException $exception) {
            return false;
        }

        return true;
    }

    private function _createTransactionRefund()
    {
        return $this->refundProvider->getTransactionRefund(
            $this->transactionScope->getTransaction()->getSessionId(),
            $this->transactionScope->getTransaction()->getAmount(),
            'Another transaction payed'
        );
    }

    private function _markAsReceived()
    {
        $this->transactionScope->getTransaction()->setP24IdOrder($this->transactionData->getOrderId());
        $this->transactionScope->getTransaction()->setReceived(date('Y-m-d H:i:s'));
        $result = Przlewy24AccountModel::addTransaction($this->transactionScope->getTransaction());
        $result &= Przlewy24AccountModel::addOrderPayed($this->transactionScope->getTransaction()->getPsIdOrder());

        return $result;
    }

    private function _verifyTransaction()
    {
        $this->transactionVerifyHandler->handle($this->transactionData);
    }

    private function _addPaymentToOrder()
    {
        $payments = $this->transactionScope->getOrder()->getOrderPaymentCollection();
        if ($payments && $payments->count() === 1) {
            $payment = $payments->getFirst();
            if ($payment instanceof \OrderPayment) {
                $payment->transaction_id = $this->transactionScope->getTransaction()->getP24IdOrder();
                $payment->update();
            }
        }
    }

    private function _changeStatus()
    {
        return $this->transactionScope->getOrder()->setCurrentState($this->transactionScope->getConfig()->getState()->getIdStateAfterPayment());
    }

    private function _createOrder()
    {
        $this->orderCreator->createOrder($this->transactionScope->getCart(), $this->transactionScope->getCustomer(), $this->transactionScope->getConfig());
        $id_order = \Order::getIdByCartId($this->transactionScope->getCart()->id);
        if ($id_order) {
            $transaction = $this->transactionScope->getTransaction();
            $transaction->setPsIdOrder($id_order);
            $order = new \Order($id_order);
            $this->transactionScope->setOrder($order);
        }
    }
}
