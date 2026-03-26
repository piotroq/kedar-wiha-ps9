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

class Przelewy24Transaction implements DbInterface
{
    private $session_id;
    private $session_hash;

    private $id_account;

    private $test_mode = false;

    private $merchant_id;

    private $shop_id;

    private $id_payment;

    private $id_cart;

    private $p24_id_order;

    private $ps_id_order;

    private $amount;

    private $received;

    private $date_add;

    private $save_card = false;

    private $crc;
    private $isoCurrency;

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setAmount(?int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIdCart(): ?int
    {
        return $this->id_cart;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setIdCart(?int $id_cart)
    {
        $this->id_cart = $id_cart;

        return $this;
    }

    public function getP24IdOrder(): ?string
    {
        return $this->p24_id_order;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setP24IdOrder(?string $p24_id_order)
    {
        $this->p24_id_order = $p24_id_order;

        return $this;
    }

    public function getPsIdOrder(): ?int
    {
        return $this->ps_id_order;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setPsIdOrder(?int $ps_id_order)
    {
        $this->ps_id_order = $ps_id_order;

        return $this;
    }

    public function getReceived(): ?string
    {
        return $this->received;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setReceived(?string $received)
    {
        $this->received = $received;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setSessionId(?string $session_id)
    {
        $this->session_id = $session_id;

        return $this;
    }

    public function getSessionHash(): ?string
    {
        return $this->session_hash;
    }

    public function setSessionHash(?string $session_hash)
    {
        $this->session_hash = $session_hash;

        return $this;
    }

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getTestMode(): bool
    {
        return $this->test_mode;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setTestMode(bool $test_mode)
    {
        $this->test_mode = $test_mode;

        return $this;
    }

    public function getMerchantId()
    {
        return $this->merchant_id;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setMerchantId($merchant_id)
    {
        $this->merchant_id = $merchant_id;

        return $this;
    }

    public function getShopId()
    {
        return $this->shop_id;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;

        return $this;
    }

    public function getIdPayment()
    {
        return $this->id_payment;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setIdPayment($id_payment)
    {
        $this->id_payment = $id_payment;

        return $this;
    }

    public function getDateAdd(): ?string
    {
        if (empty($this->date_add)) {
            return date('Y-m-d H:i:s');
        }

        return $this->date_add;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setDateAdd(?string $date_add)
    {
        $this->date_add = $date_add;

        return $this;
    }

    public function getSaveCard(): bool
    {
        return $this->save_card;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setSaveCard(bool $save_card)
    {
        $this->save_card = $save_card;

        return $this;
    }

    public function getCrc(): ?string
    {
        return $this->crc;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setCrc(?string $crc)
    {
        $this->crc = $crc;

        return $this;
    }

    public function getIsoCurrency(): ?string
    {
        return $this->isoCurrency;
    }

    /**
     * @return Przelewy24Transaction
     */
    public function setIsoCurrency(?string $isoCurrency)
    {
        $this->isoCurrency = $isoCurrency;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_transaction';
    }

    public function getDatabaseFieldsArray(): array
    {
        $p24_id_order = $this->getP24IdOrder() ? pSQL($this->getP24IdOrder()) : null;
        $ps_id_order = $this->getPsIdOrder();
        $received = $this->getReceived() ? pSQL($this->getReceived()) : null;

        return [
            'session_id' => pSQL($this->getSessionId()),
            'session_hash' => pSQL($this->getSessionHash()),
            'id_account' => (int) $this->getIdAccount(),
            'test_mode' => (bool) $this->getTestMode(),
            'merchant_id' => (int) $this->getMerchantId(),
            'id_payment' => (int) $this->getIdPayment(),
            'shop_id' => pSQL($this->getShopId()),
            'id_cart' => (int) $this->getIdCart(),
            'p24_id_order' => $p24_id_order,
            'ps_id_order' => $ps_id_order,
            'amount' => (int) $this->getAmount(),
            'received' => $received,
            'date_add' => pSQL($this->getDateAdd()),
            'save_card' => (bool) $this->getSaveCard(),
            'iso_currency' => pSQL($this->getIsoCurrency()),
            'crc' => pSQL($this->getCrc()),
        ];
    }
}
