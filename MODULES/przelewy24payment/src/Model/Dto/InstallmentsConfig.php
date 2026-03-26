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

class InstallmentsConfig implements DbInterface
{
    private $id_account;

    private $enable_calculator_on_product = false;

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return InstallmentsConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function isEnableCalculatorOnProduct(): bool
    {
        return $this->enable_calculator_on_product;
    }

    public function setEnableCalculatorOnProduct(bool $enable_calculator_on_product): InstallmentsConfig
    {
        $this->enable_calculator_on_product = $enable_calculator_on_product;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_installments_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'enable_calculator_on_product' => (bool) $this->isEnableCalculatorOnProduct(),
        ];
    }
}
