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

class CardsConfig implements DbInterface
{
    private $id_account;

    private $one_click_card;

    private $click_to_pay;

    private $payment_in_store;

    private $click_to_pay_guest;

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
     * @return CardsConfig
     */
    public function setIdAccount($id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOneClickCard()
    {
        return $this->one_click_card;
    }

    /**
     * @param mixed $one_click_card
     *
     * @return CardsConfig
     */
    public function setOneClickCard($one_click_card)
    {
        $this->one_click_card = $one_click_card;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClickToPay()
    {
        return $this->click_to_pay;
    }

    /**
     * @param mixed $click_to_pay
     *
     * @return CardsConfig
     */
    public function setClickToPay($click_to_pay)
    {
        $this->click_to_pay = $click_to_pay;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentInStore()
    {
        return $this->payment_in_store;
    }

    /**
     * @param mixed $payment_in_store
     *
     * @return CardsConfig
     */
    public function setPaymentInStore($payment_in_store)
    {
        $this->payment_in_store = $payment_in_store;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClickToPayGuest()
    {
        return $this->click_to_pay_guest;
    }

    /**
     * @param mixed $click_to_pay_guest
     *
     * @return CardsConfig
     */
    public function setClickToPayGuest($click_to_pay_guest)
    {
        $this->click_to_pay_guest = $click_to_pay_guest;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_cards_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'one_click_card' => (bool) $this->getOneClickCard(),
            'click_to_pay' => (bool) $this->getClickToPay(),
            'payment_in_store' => (bool) $this->getPaymentInStore(),
            'click_to_pay_guest' => (bool) $this->getClickToPayGuest(),
        ];
    }
}
