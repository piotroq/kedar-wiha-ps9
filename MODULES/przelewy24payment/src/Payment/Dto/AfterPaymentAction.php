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

namespace Przelewy24\Payment\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AfterPaymentAction
{
    private $errors = [];

    private $params = [];

    private $redirect;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): AfterPaymentAction
    {
        $this->errors = $errors;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): AfterPaymentAction
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param mixed $redirect
     *
     * @return AfterPaymentAction
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }
}
