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

class ExtraChargeConfig implements DbInterface
{
    private $id_account;

    private $extra_charge_amount = 0;

    private $extra_charge_percent = 0;

    public function getExtraChargeAmount(): ?float
    {
        return $this->extra_charge_amount;
    }

    /**
     * @return ExtraChargeConfig
     */
    public function setExtraChargeAmount(?float $extra_charge_amount)
    {
        $this->extra_charge_amount = $extra_charge_amount;

        return $this;
    }

    public function getExtraChargePercent(): ?int
    {
        return $this->extra_charge_percent;
    }

    /**
     * @return ExtraChargeConfig
     */
    public function setExtraChargePercent(?int $extra_charge_percent)
    {
        $this->extra_charge_percent = $extra_charge_percent;

        return $this;
    }

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return ExtraChargeConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_extra_charge_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'extra_charge_amount' => (float) $this->getExtraChargeAmount(),
            'extra_charge_percent' => (int) $this->getExtraChargePercent(),
        ];
    }
}
