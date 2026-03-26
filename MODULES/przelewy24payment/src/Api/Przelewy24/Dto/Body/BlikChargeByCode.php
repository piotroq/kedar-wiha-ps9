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

class BlikChargeByCode implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $token;
    private $blikCode;
    private $aliasValue;
    private $aliasLabel;
    private $recurring;

    public function getAliasLabel()
    {
        return $this->aliasLabel;
    }

    /**
     * @return BlikChargeByCode
     */
    public function setAliasLabel($aliasLabel)
    {
        $this->aliasLabel = $aliasLabel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAliasValue()
    {
        return $this->aliasValue;
    }

    /**
     * @return BlikChargeByCode
     */
    public function setAliasValue($aliasValue)
    {
        $this->aliasValue = $aliasValue;

        return $this;
    }

    public function getBlikCode(): ?string
    {
        return $this->blikCode;
    }

    /**
     * @return BlikChargeByCode
     */
    public function setBlikCode(string $blikCode)
    {
        $this->blikCode = $blikCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * @param mixed $recurring
     *
     * @return BlikChargeByCode
     */
    public function setRecurring($recurring)
    {
        $this->recurring = $recurring;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return BlikChargeByCode
     */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }
}
