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

namespace Przelewy24\Manager\PaymentOptions\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigPaymentOptions
{
    private $cart;

    private $currency;

    private $paymentMethodsCollection;

    private $model;

    private $config;

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param mixed $cart
     *
     * @return ConfigPaymentOptions
     */
    public function setCart($cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     *
     * @return ConfigPaymentOptions
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     *
     * @return ConfigPaymentOptions
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethodsCollection()
    {
        return $this->paymentMethodsCollection;
    }

    /**
     * @param mixed $paymentMethodsCollection
     *
     * @return ConfigPaymentOptions
     */
    public function setPaymentMethodsCollection($paymentMethodsCollection)
    {
        $this->paymentMethodsCollection = $paymentMethodsCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     *
     * @return ConfigPaymentOptions
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }
}
