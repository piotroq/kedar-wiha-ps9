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

class TimeConfig implements DbInterface
{
    private $id_account;

    private $wait_for_result = false;

    private $time_limit;

    private $time_limit_fast_transfer;

    private $time_limit_long_term;

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return TimeConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->time_limit;
    }

    /**
     * @return TimeConfig
     */
    public function setTimeLimit(?int $time_limit)
    {
        $this->time_limit = $time_limit;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWaitForResult(): bool
    {
        return $this->wait_for_result;
    }

    /**
     * @param bool $wait_for_result
     *
     * @return TimeConfig
     */
    public function setWaitForResult(bool $wait_for_result)
    {
        $this->wait_for_result = $wait_for_result;

        return $this;
    }

    public function getTimeLimitFastTransfer(): ?int
    {
        return $this->time_limit_fast_transfer;
    }

    /**
     * @return TimeConfig
     */
    public function setTimeLimitFastTransfer(?int $time_limit_fast_transfer)
    {
        $this->time_limit_fast_transfer = $time_limit_fast_transfer;

        return $this;
    }

    public function getTimeLimitLongTerm(): ?int
    {
        return $this->time_limit_long_term;
    }

    /**
     * @return TimeConfig
     */
    public function setTimeLimitLongTerm(?int $time_limit_long_term)
    {
        $this->time_limit_long_term = $time_limit_long_term;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_time_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'wait_for_result' => (bool) $this->getWaitForResult(),
            'time_limit' => (int) $this->getTimeLimit(),
            'time_limit_fast_transfer' => (int) $this->getTimeLimitFastTransfer(),
            'time_limit_long_term' => (int) $this->getTimeLimitLongTerm(),
        ];
    }
}
