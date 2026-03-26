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

class GoogleConfig implements DbInterface
{
    private $id_account;

    private $id_merchant;

    private $merchant_name;

    private $one_click;

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
     * @return GoogleConfig
     */
    public function setIdAccount($id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdMerchant()
    {
        return $this->id_merchant;
    }

    /**
     * @param mixed $id_merchant
     *
     * @return GoogleConfig
     */
    public function setIdMerchant($id_merchant)
    {
        $this->id_merchant = $id_merchant;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMerchantName()
    {
        return $this->merchant_name;
    }

    /**
     * @param mixed $merchant_name
     *
     * @return GoogleConfig
     */
    public function setMerchantName($merchant_name)
    {
        $this->merchant_name = $merchant_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOneClick()
    {
        return $this->one_click;
    }

    /**
     * @param mixed $one_click
     *
     * @return GoogleConfig
     */
    public function setOneClick($one_click)
    {
        $this->one_click = $one_click;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_google_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'id_merchant' => pSQL($this->getIdMerchant()),
            'merchant_name' => pSQL($this->getMerchantName()),
            'one_click' => (bool) $this->getOneClick(),
        ];
    }
}
