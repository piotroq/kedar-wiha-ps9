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

use Przelewy24\Dto\BlikNotify;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Order\OrderCreator;

class BlikTransactionHandler
{
    private $transactionScopeFactory;
    private $orderCreator;

    public function __construct(
        TransactionScopeFactory $transactionScopeFactory,
        OrderCreator $orderCreator
    ) {
        $this->transactionScopeFactory = $transactionScopeFactory;
        $this->orderCreator = $orderCreator;
    }

    public function handle(BlikNotify $blikNotify)
    {
        $transactionScope = $this->transactionScopeFactory->factory($blikNotify->getSessionId());
        if (!$blikNotify->validSign($transactionScope->getTransaction()->getCrc())) {
            exit('wrong sign');
        }

        Przlewy24AccountModel::addBlikTransaction($blikNotify);

        if ($blikNotify->getResult()['error'] == '0' && $blikNotify->getResult()['status'] == 'AUTHORIZED') {
            $this->orderCreator->createOrder($transactionScope->getCart(), $transactionScope->getCustomer(), $transactionScope->getConfig());
            $id_order = \Order::getIdByCartId($transactionScope->getCart()->id);
            if ($id_order) {
                $transaction = $transactionScope->getTransaction();
                $transaction->setPsIdOrder($id_order);
                $order = new \Order($id_order);
                $transactionScope->setOrder($order);
                Przlewy24AccountModel::addTransaction($transaction);
            }
        }
    }
}
