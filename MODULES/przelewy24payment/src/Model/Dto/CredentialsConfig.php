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

class CredentialsConfig implements DbInterface
{
    private $id_account;

    private $id_merchant;

    private $salt;

    private $api_key;

    private $test_mode = false;

    public function getIdAccount(): ?int
    {
        return $this->id_account === null ? null : $this->id_account;
    }

    /**
     * @return CredentialsConfig
     */
    public function setIdAccount(int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key === null ? null : trim($this->api_key);
    }

    /**
     * @return CredentialsConfig
     */
    public function setApiKey(?string $api_key)
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function getIdMerchant(): ?string
    {
        return $this->id_merchant === null ? null : trim($this->id_merchant);
    }

    /**
     * @return CredentialsConfig
     */
    public function setIdMerchant(?string $id_merchant)
    {
        $this->id_merchant = $id_merchant;

        return $this;
    }

    public function getShopId(): ?string
    {
        return $this->id_merchant === null ? null : trim($this->id_merchant);
    }

    /**
     * @return CredentialsConfig
     */
    public function setShopId(?string $shop_id)
    {
        $this->id_merchant = $shop_id;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @return CredentialsConfig
     */
    public function setSalt(?string $salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function getTestMode(): bool
    {
        return $this->test_mode;
    }

    /**
     * @return CredentialsConfig
     */
    public function setTestMode(bool $test_mode)
    {
        $this->test_mode = $test_mode;

        return $this;
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'id_merchant' => (int) $this->getIdMerchant(),
            'shop_id' => pSQL($this->getIdMerchant()),
            'salt' => pSQL($this->getSalt()),
            'api_key' => pSQL($this->getApiKey()),
            'test_mode' => (bool) $this->getTestMode(),
        ];
    }

    public function getTableName(): string
    {
        return 'przelewy24_credentials_config';
    }
}
