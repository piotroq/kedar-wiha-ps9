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

namespace Przelewy24\Migrator\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Account
{
    private $idCurrency;
    private $idShop;
    private $testMode;

    private $credentials;
    private $extraCharge;
    private $isoCode;
    private $shopName;

    public function __construct(int $idShop, string $shopName, int $idCurrency, string $isoCode, bool $testMode)
    {
        $this->idCurrency = $idCurrency;
        $this->idShop = $idShop;
        $this->testMode = $testMode;
        $this->isoCode = $isoCode;
        $this->shopName = $shopName;
    }

    public function getId(): string
    {
        return $this->idShop . '-' . $this->idCurrency;
    }

    public function getIdCurrency(): int
    {
        return $this->idCurrency;
    }

    public function getIdShop(): int
    {
        return $this->idShop;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getCredentials(): ?Credentials
    {
        return $this->credentials;
    }

    public function setCredentials(Credentials $credentials): Account
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getExtraCharge(): ?ExtraCharge
    {
        return $this->extraCharge;
    }

    public function setExtraCharge(ExtraCharge $extraCharge): Account
    {
        $this->extraCharge = $extraCharge;

        return $this;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setIsoCode(string $isoCode): Account
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    public function getShopName(): string
    {
        return $this->shopName;
    }

    public function setShopName(string $shopName): Account
    {
        $this->shopName = $shopName;

        return $this;
    }
}
