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

namespace Przelewy24\Api\Przelewy24\Dto\Body;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;

class TransactionVerify implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $merchantId;

    private $posId;

    private $sessionId;

    private $amount;

    private $currency;

    private $orderId;

    private $sign;

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return TransactionVerify
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return TransactionVerify
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    /**
     * @param int $merchantId
     *
     * @return TransactionVerify
     */
    public function setMerchantId(int $merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     *
     * @return TransactionVerify
     */
    public function setOrderId(int $orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getPosId(): ?int
    {
        return $this->posId;
    }

    /**
     * @param int $posId
     *
     * @return TransactionVerify
     */
    public function setPosId(int $posId)
    {
        $this->posId = $posId;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return TransactionVerify
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    /**
     * @param string $sign
     *
     * @return TransactionVerify
     */
    public function setSign(string $sign)
    {
        $this->sign = $sign;

        return $this;
    }

    public function calculateSign($crc)
    {
        $data = [
            'sessionId' => $this->sessionId,
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'crc' => $crc,
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);
        $this->sign = $sign;
    }
}
