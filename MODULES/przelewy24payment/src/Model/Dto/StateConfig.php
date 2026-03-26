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

class StateConfig implements DbInterface
{
    private $id_account;

    private $id_state_after_payment;

    private $id_state_before_payment;

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return StateConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getIdStateAfterPayment(): ?int
    {
        return $this->id_state_after_payment;
    }

    /**
     * @return StateConfig
     */
    public function setIdStateAfterPayment(?int $id_state_after_payment)
    {
        $this->id_state_after_payment = $id_state_after_payment;

        return $this;
    }

    public function getIdStateBeforePayment(): ?int
    {
        return $this->id_state_before_payment;
    }

    /**
     * @return StateConfig
     */
    public function setIdStateBeforePayment(?int $id_state_before_payment)
    {
        $this->id_state_before_payment = $id_state_before_payment;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_state_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'id_state_after_payment' => (int) $this->getIdStateAfterPayment(),
            'id_state_before_payment' => (int) $this->getIdStateBeforePayment(),
        ];
    }
}
