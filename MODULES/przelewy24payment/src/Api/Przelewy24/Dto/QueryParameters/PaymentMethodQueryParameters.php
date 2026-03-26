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

namespace Przelewy24\Api\Przelewy24\Dto\QueryParameters;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\QueryParameters\Interfaces\QueryParametersInterface;

class PaymentMethodQueryParameters implements QueryParametersInterface
{
    private $lang;

    private $amount;

    private $currency;

    public function buildQuery(): string
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $var) {
            if (empty($var) || $key === 'lang') {
                unset($vars[$key]);
            }
        }

        return $this->lang . '/?' . http_build_query($vars);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return PaymentMethodQueryParameters
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     * @return PaymentMethodQueryParameters
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     *
     * @return PaymentMethodQueryParameters
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }
}
