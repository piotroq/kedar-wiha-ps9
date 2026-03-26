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

namespace Przelewy24\Manager\PaymentOptions\Presenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Przelewy24\Dto\PaymentMethod;

class Blik0PaymentOptionPresenter extends AbstractPaymentOptionPresenter
{
    protected function _createPaymentOption(?PaymentMethod $paymentMethod): PaymentOption
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setAction($paymentMethod->getFrontUrl());
        $paymentOption->setLogo($paymentMethod->getImgUrl());
        $paymentOption->setModuleName('przelewy24');
        $paymentOption->setCallToActionText($paymentMethod->getSpecialName());

        return $paymentOption;
    }

    protected function _createAdditionalInformation(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod)
    {
        $approveFinder = new \ConditionsToApproveFinder(
            $this->context,
            $this->translator
        );
        $this->context->smarty->assign(
            [
                'conditions_to_approve' => $approveFinder->getConditionsToApproveForTemplate(),
                'selected' => $this->lastPaymentMethodCookie->getLastIdPayment() == $paymentMethod->getId(),
                'form_action' => $paymentMethod->getFrontUrl(),
            ]);
        $paymentOption->setAdditionalInformation($this->context->smarty->fetch('module:przelewy24payment/views/templates/front/payment_blik_0_additional_information.tpl'));
    }

    protected function _createForm(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod)
    {
    }
}
