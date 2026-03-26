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

class BlikConfig implements DbInterface
{
    private $id_account;

    private $blik_level_0;

    /**
     * @return mixed
     */
    public function getIdAccount()
    {
        return $this->id_account;
    }

    /**
     * @param mixed $id_account
     *
     * @return BlikConfig
     */
    public function setIdAccount($id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlikLevel0()
    {
        return $this->blik_level_0;
    }

    /**
     * @param mixed $blik_level_0
     *
     * @return BlikConfig
     */
    public function setBlikLevel0($blik_level_0)
    {
        $this->blik_level_0 = $blik_level_0;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_blik_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'blik_level_0' => (bool) $this->getBlikLevel0(),
        ];
    }
}
