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
use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Dto\PaymentMethod;

class MainPaymentOptionPresenter extends AbstractPaymentOptionPresenter
{
    protected function _createPaymentOption(?PaymentMethod $paymentMethod): PaymentOption
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setAction($this->urlHelper->getUrlFrontPayment(0, $this->configPaymentOptions->getCart()->id));
        $paymentOption->setModuleName('przelewy24');
        $paymentOption->setLogo($this->imageHelper->createUrl('przelewy24-logo.svg'));
        $paymentOption->setCallToActionText('Przelewy24');

        return $paymentOption;
    }

    protected function _createAdditionalInformation(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod)
    {
        $this->context->smarty->assign(
            [
                'paymentsMainList' => $this->_getMainPayments(),
                'selected' => $this->lastPaymentMethodCookie->getLastIdPayment() === 0,
                'payment_method_id' => 0,
            ]);
        $paymentOption->setAdditionalInformation($this->context->smarty->fetch('module:przelewy24payment/views/templates/front/payment_option.tpl'));
    }

    protected function _createForm(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod)
    {
        if ($this->configPaymentOptions->getConfig()->getOrder()->getAcceptInShop()) {
            $this->context->smarty->assign(
                [
                    'form_action' => $paymentOption->getAction(),
                ]);
            $paymentOption->setForm($this->context->smarty->fetch('module:przelewy24payment/views/templates/front/payment_regulations.tpl'));
        }
    }

    private function _getMainPayments()
    {
        if ($this->configPaymentOptions->getConfig()->getPayment()->getPaymentMethodInMain()) {
            $idsMainPaymentMethods = $this->configPaymentOptions->getModel()->getPaymentMethodMain();
            $mainMethodsCollection = $this->configPaymentOptions->getPaymentMethodsCollection()->intersectByIds($idsMainPaymentMethods);
            $this->_removeSpecialPaymentMethod($mainMethodsCollection);
            foreach ($mainMethodsCollection as $paymentMethod) {
                $paymentMethod->setFrontUrl($this->urlHelper->getUrlFrontPayment($paymentMethod->getId(), $this->configPaymentOptions->getCart()->id));
            }

            return $mainMethodsCollection;
        }

        return new PaymentMethodCollection();
    }

    private function _removeSpecialPaymentMethod(PaymentMethodCollection $mainMethodsCollection)
    {
        if ($this->configPaymentOptions->getConfig()->getCards()->getPaymentInStore()) {
            $mainMethodsCollection->removeTypePayment(PaymentTypeEnum::CARD_PAYMENT);
        }
        if ($this->configPaymentOptions->getConfig()->getGoogle()->getOneClick()) {
            $mainMethodsCollection->removeTypePayment(PaymentTypeEnum::GOOGLE_PAYMENT);
        }
        if ($this->configPaymentOptions->getConfig()->getApple()->getOneClick()) {
            $mainMethodsCollection->removeTypePayment(PaymentTypeEnum::APPLE_PAYMENT);
        }
    }
}
