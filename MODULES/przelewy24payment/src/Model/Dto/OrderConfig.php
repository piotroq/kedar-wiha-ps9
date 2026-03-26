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

class OrderConfig implements DbInterface
{
    private $id_account;

    private $alter_stock = false;

    private $create_order;

    private $standard_return_page = false;

    private $skip_confirmation = false;

    private $accept_in_shop = true;

    private $intro_text = false;

    private $order_identification;

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return OrderConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getAcceptInShop(): bool
    {
        return $this->accept_in_shop;
    }

    /**
     * @return OrderConfig
     */
    public function setAcceptInShop(bool $accept_in_shop)
    {
        $this->accept_in_shop = $accept_in_shop;

        return $this;
    }

    public function getIntroText(): bool
    {
        return $this->intro_text;
    }

    /**
     * @return OrderConfig
     */
    public function setIntroText(bool $intro_text)
    {
        $this->intro_text = $intro_text;

        return $this;
    }

    public function getOrderIdentification(): ?string
    {
        return $this->order_identification;
    }

    /**
     * @return OrderConfig
     */
    public function setOrderIdentification(?string $order_identification)
    {
        $this->order_identification = $order_identification;

        return $this;
    }

    public function getAlterStock(): bool
    {
        return $this->alter_stock;
    }

    /**
     * @return OrderConfig
     */
    public function setAlterStock(bool $alter_stock)
    {
        $this->alter_stock = $alter_stock;

        return $this;
    }

    public function getSkipConfirmation(): bool
    {
        return $this->skip_confirmation;
    }

    /**
     * @return OrderConfig
     */
    public function setSkipConfirmation(bool $skip_confirmation)
    {
        $this->skip_confirmation = $skip_confirmation;

        return $this;
    }

    public function getStandardReturnPage(): bool
    {
        return $this->standard_return_page;
    }

    /**
     * @return OrderConfig
     */
    public function setStandardReturnPage(bool $standard_return_page)
    {
        $this->standard_return_page = $standard_return_page;

        return $this;
    }

    public function getCreateOrder(): ?string
    {
        return $this->create_order;
    }

    /**
     * @return OrderConfig
     */
    public function setCreateOrder(?string $create_order)
    {
        $this->create_order = $create_order;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_order_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'alter_stock' => (bool) $this->getAlterStock(),
            'create_order' => (string) $this->getCreateOrder(),
            'intro_text' => (bool) $this->getIntroText(),
            'order_identification' => (string) $this->getOrderIdentification(),
            'standard_return_page' => (bool) $this->getStandardReturnPage(),
            'skip_confirmation' => (bool) $this->getSkipConfirmation(),
            'accept_in_shop' => (bool) $this->getAcceptInShop(),
        ];
    }
}
