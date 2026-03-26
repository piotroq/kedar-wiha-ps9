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

class Refund implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $orderId;

    private $sessionId;

    private $amount;

    private $description;

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return Refund
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Refund
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     *
     * @return Refund
     */
    public function setOrderId(int $orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return Refund
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }
}
