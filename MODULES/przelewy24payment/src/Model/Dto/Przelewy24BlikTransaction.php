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

namespace Przelewy24\Model\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Dto\Interfaces\DbInterface;

class Przelewy24BlikTransaction implements DbInterface
{
    private $session_id;

    private $order_id;

    private $method;

    private $error;

    private $message;

    private $status;
    private $trx_ref;
    private $date_add;

    public function getDateAdd(): ?string
    {
        if (empty($this->date_add)) {
            return date('Y-m-d H:i:s');
        }

        return $this->date_add;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setDateAdd(?string $date_add)
    {
        $this->date_add = $date_add;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setError(string $error)
    {
        $this->error = $error;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMethod(): ?int
    {
        return $this->method;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->order_id;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setOrderId(int $order_id)
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setSessionId(string $session_id)
    {
        $this->session_id = $session_id;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getTrxRef(): ?string
    {
        return $this->trx_ref;
    }

    /**
     * @return Przelewy24BlikTransaction
     */
    public function setTrxRef(?string $trx_ref)
    {
        $this->trx_ref = $trx_ref;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_blik_transaction';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'session_id' => pSQL($this->getSessionId()),
            'order_id' => (int) $this->getOrderId(),
            'method' => (int) $this->getMethod(),
            'error' => pSQL($this->getError()),
            'message' => pSQL($this->getMessage()),
            'status' => pSQL($this->getStatus()),
            'trx_ref' => pSQL($this->getTrxRef()),
            'date_add' => pSQL($this->getDateAdd()),
        ];
    }
}
