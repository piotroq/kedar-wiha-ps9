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

class Product implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $sellerId;

    private $sellerCategory;

    private $name;

    private $description;

    private $quantity;

    private $price;

    private $number;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Product
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Product
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return Product
     */
    public function setNumber(int $number)
    {
        $this->number = $number;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Product
     */
    public function setPrice(int $price)
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Product
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSellerCategory(): ?int
    {
        return $this->sellerCategory;
    }

    /**
     * @param int $sellerCategory
     *
     * @return Product
     */
    public function setSellerCategory(int $sellerCategory)
    {
        $this->sellerCategory = $sellerCategory;

        return $this;
    }

    public function getSellerId(): ?int
    {
        return $this->sellerId;
    }

    /**
     * @param int $sellerId
     *
     * @return Product
     */
    public function setSellerId(int $sellerId)
    {
        $this->sellerId = $sellerId;

        return $this;
    }
}
