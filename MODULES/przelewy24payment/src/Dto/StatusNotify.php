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

use Przelewy24\Dto\Interfaces\TransactionVerifyFullDataInterface;

class StatusNotify implements TransactionVerifyFullDataInterface
{
    private $merchantId;

    private $posId;

    private $sessionId;

    private $amount;

    private $originAmount;

    private $currency;

    private $orderId;

    private $methodId;

    private $statement;

    private $sign;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return StatusNotify
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     *
     * @return StatusNotify
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param mixed $merchantId
     *
     * @return StatusNotify
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethodId()
    {
        return $this->methodId;
    }

    /**
     * @param mixed $methodId
     *
     * @return StatusNotify
     */
    public function setMethodId($methodId)
    {
        $this->methodId = $methodId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     *
     * @return StatusNotify
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginAmount()
    {
        return $this->originAmount;
    }

    /**
     * @param mixed $originAmount
     *
     * @return StatusNotify
     */
    public function setOriginAmount($originAmount)
    {
        $this->originAmount = $originAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosId()
    {
        return $this->posId;
    }

    /**
     * @param mixed $posId
     *
     * @return StatusNotify
     */
    public function setPosId($posId)
    {
        $this->posId = $posId;

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
     * @return StatusNotify
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * @param mixed $sign
     *
     * @return StatusNotify
     */
    public function setSign($sign)
    {
        $this->sign = $sign;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param mixed $statement
     *
     * @return StatusNotify
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;

        return $this;
    }

    public function validSign(string $crc): bool
    {
        $data = [
            'merchantId' => (int) $this->merchantId,
            'posId' => (int) $this->posId,
            'sessionId' => (string) $this->sessionId,
            'amount' => (int) $this->amount,
            'originAmount' => (int) $this->originAmount,
            'currency' => (string) $this->currency,
            'orderId' => (int) $this->orderId,
            'methodId' => (int) $this->methodId,
            'statement' => (string) $this->statement,
            'crc' => $crc,
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);

        return $sign === $this->getSign();
    }
}
