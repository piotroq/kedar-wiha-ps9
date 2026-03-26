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

namespace Przelewy24\Factory\Transaction;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\AccountNotFoundApiException;
use Przelewy24\Dto\TransactionScope;
use Przelewy24\Exceptions\CartNotExistException;
use Przelewy24\Exceptions\CustomerNotExistException;
use Przelewy24\Exceptions\OrderNotExistException;
use Przelewy24\Exceptions\WrongSessionIdException;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Translator\Adapter\Translator;

class TransactionScopeFactory
{
    private static $instances;

    private $transaction;

    private $model;

    private $config;

    private $connection;

    private $cart;

    private $customer;

    private $order;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(ConnectionFactory $connectionFactory, Przelewy24Config $config, Translator $translator)
    {
        $this->config = $config;
        $this->connectionFactory = $connectionFactory;
        $this->translator = $translator;
    }

    public function factory($sessionId): TransactionScope
    {
        if (isset(self::$instances[$sessionId])) {
            return self::$instances[$sessionId];
        }
        $this->_getTransaction($sessionId);
        $this->_createModel();
        $this->_createConfig();
        $this->_createConnection();
        $this->_createCart();
        $this->_createOrder();
        $this->_createCustomer();
        $this->_returnScope();

        return self::$instances[$this->transaction->getSessionId()];
    }

    private function _getTransaction($sessionId)
    {
        $this->transaction = Przlewy24AccountModel::getTransaction($sessionId);
        if (empty($this->transaction->getSessionId()) || $this->transaction->getSessionId() != $sessionId) {
            throw new WrongSessionIdException('Wrong session id', $this->translator->trans('Wrong session id', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    private function _createConnection()
    {
        $this->connection = $this->connectionFactory->factory($this->model);
    }

    private function _createModel()
    {
        $this->model = new Przlewy24AccountModel($this->transaction->getIdAccount());
        if (!\Validate::isLoadedObject($this->model)) {
            throw new AccountNotFoundApiException('Account not found', $this->translator->trans('Account not found', [], 'Modules.Przelewy24payment.Exception'));
        }
        $this->model->test_mode = $this->transaction->getTestMode();
    }

    private function _createConfig()
    {
        $this->config->setAccount($this->model, false);
    }

    private function _createCart()
    {
        $this->cart = new \Cart($this->transaction->getIdCart());
        if (!\Validate::isLoadedObject($this->cart)) {
            throw new CartNotExistException('Cart not found', $this->translator->trans('Cart not found', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    private function _createCustomer()
    {
        $this->customer = new \Customer($this->cart->id_customer);
        if (!\Validate::isLoadedObject($this->customer)) {
            throw new CustomerNotExistException('Customer not found', $this->translator->trans('Customer not found', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    private function _createOrder()
    {
        $this->order = null;
        if (!empty($this->transaction->getPsIdOrder())) {
            $this->order = new \Order($this->transaction->getPsIdOrder());
        }
        if (!\Validate::isLoadedObject($this->cart)) {
            throw new OrderNotExistException('Order not found', $this->translator->trans('Order not found', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    private function _returnScope()
    {
        $transactionScope = new TransactionScope();
        $transactionScope->setTransaction($this->transaction);
        $transactionScope->setModel($this->model);
        $transactionScope->setConnection($this->connection);
        $transactionScope->setConfig($this->config);
        $transactionScope->setCart($this->cart);
        $transactionScope->setCustomer($this->customer);
        $transactionScope->setOrder($this->order);
        $transactionScope->setSessionId($this->transaction->getSessionId());
        self::$instances[$this->transaction->getSessionId()] = $transactionScope;
    }
}
