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

use Przelewy24\Model\Dto\Przelewy24Transaction;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransactionScope
{
    private $sessionId;

    private $transaction;

    private $model;

    private $config;

    private $connection;

    private $cart;

    private $customer;

    private $order;

    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param mixed $cart
     *
     * @return TransactionScope
     */
    public function setCart($cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     *
     * @return TransactionScope
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     *
     * @return TransactionScope
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     *
     * @return TransactionScope
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     *
     * @return TransactionScope
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     *
     * @return TransactionScope
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getTransaction(): ?Przelewy24Transaction
    {
        return $this->transaction;
    }

    /**
     * @param mixed $transaction
     *
     * @return TransactionScope
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     *
     * @return TransactionScope
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function validSign(string $sign): bool
    {
        $data = [
            'sessionId' => $this->sessionId,
            'merchantId' => $this->transaction->getMerchantId(),
            'amount' => $this->transaction->getAmount(),
            'currency' => $this->transaction->getIsoCurrency(),
            'crc' => $this->transaction->getCrc(),
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $generatedSign = hash('sha384', $string);

        return $generatedSign === $sign;
    }

    public function validSignOrder(string $sign): bool
    {
        $data = [
            'sessionId' => $this->sessionId,
            'orderId' => '4299921258',
            'amount' => $this->transaction->getAmount(),
            'currency' => $this->transaction->getIsoCurrency(),
            'crc' => $this->transaction->getCrc(),
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $generatedSign = hash('sha384', $string);

        return $generatedSign === $sign;
    }
}
