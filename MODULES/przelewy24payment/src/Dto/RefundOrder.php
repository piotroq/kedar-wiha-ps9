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

use Przelewy24\Collection\RefundOrderProductCollection;

class RefundOrder
{
    private $sessionId;

    private $idOrder;

    private $amount;

    private $products;

    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return RefundOrder
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return RefundOrder
     */
    public function setProducts(RefundOrderProductCollection $products)
    {
        $this->products = $products;

        return $this;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return RefundOrder
     */
    public function setSessionId(string $sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getIdOrder()
    {
        return $this->idOrder;
    }

    /**
     * @param mixed $idOrder
     *
     * @return RefundOrder
     */
    public function setIdOrder($idOrder)
    {
        $this->idOrder = $idOrder;

        return $this;
    }
}
