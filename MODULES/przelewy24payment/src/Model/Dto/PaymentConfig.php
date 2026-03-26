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

use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Model\Dto\Interfaces\DbInterface;

class PaymentConfig implements DbInterface
{
    private $id_account;

    private $payment_method_in_main;

    private $payment_method_separate;

    private $payment_method_in_main_list = [];

    private $payment_method_separate_list = [];

    private $payment_method_name_list = [];

    public function __construct()
    {
        $this->payment_method_name_list = new PaymentMethodCollection();
        $this->payment_method_separate_list = new PaymentMethodCollection();
        $this->payment_method_in_main_list = new PaymentMethodCollection();
    }

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    /**
     * @return PaymentConfig
     */
    public function setIdAccount(?int $id_account)
    {
        $this->id_account = $id_account;

        return $this;
    }

    public function getPaymentMethodInMain()
    {
        return $this->payment_method_in_main;
    }

    /**
     * @return PaymentConfig
     */
    public function setPaymentMethodInMain($payment_method_in_main)
    {
        $this->payment_method_in_main = $payment_method_in_main;

        return $this;
    }

    public function getPaymentMethodSeparate()
    {
        return $this->payment_method_separate;
    }

    /**
     * @return PaymentConfig
     */
    public function setPaymentMethodSeparate($payment_method_separate)
    {
        $this->payment_method_separate = $payment_method_separate;

        return $this;
    }

    public function getPaymentMethodInMainList(): PaymentMethodCollection
    {
        return $this->payment_method_in_main_list;
    }

    public function setPaymentMethodInMainList(PaymentMethodCollection $payment_method_in_main_list): PaymentConfig
    {
        $this->payment_method_in_main_list = $payment_method_in_main_list;

        return $this;
    }

    public function getPaymentMethodNameList(): PaymentMethodCollection
    {
        return $this->payment_method_name_list;
    }

    public function setPaymentMethodNameList(PaymentMethodCollection $payment_method_name_list): PaymentConfig
    {
        $this->payment_method_name_list = $payment_method_name_list;

        return $this;
    }

    public function getPaymentMethodSeparateList(): PaymentMethodCollection
    {
        return $this->payment_method_separate_list;
    }

    public function setPaymentMethodSeparateList(PaymentMethodCollection $payment_method_separate_list): PaymentConfig
    {
        $this->payment_method_separate_list = $payment_method_separate_list;

        return $this;
    }

    public function getTableName(): string
    {
        return 'przelewy24_payment_config';
    }

    public function getDatabaseFieldsArray(): array
    {
        return [
            'id_account' => (int) $this->getIdAccount(),
            'payment_method_in_main' => (bool) $this->getPaymentMethodInMain(),
            'payment_method_separate' => (bool) $this->getPaymentMethodSeparate(),
        ];
    }
}
