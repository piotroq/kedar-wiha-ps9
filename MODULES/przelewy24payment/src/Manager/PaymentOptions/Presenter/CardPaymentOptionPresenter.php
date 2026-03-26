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
use Przelewy24\Collection\CardsCollection;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Model\Przelewy24CardModel;

class CardPaymentOptionPresenter extends AbstractPaymentOptionPresenter
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
        $cards = new CardsCollection();
        if ($this->configPaymentOptions->getConfig()->getCards()->getOneClickCard()) {
            $cards = Przelewy24CardModel::getCardsByIdCustomer($this->configPaymentOptions->getCart()->id_customer);
        }

        $this->cardsLogoResolver->resolve($cards);
        $approveFinder = new \ConditionsToApproveFinder(
            $this->context,
            $this->translator
        );

        $this->context->smarty->assign(
            [
                'cards' => $cards,
                'click_to_pay_guest' => $this->configPaymentOptions->getConfig()->getCards()->getClickToPayGuest(),
                'click_to_pay' => $this->configPaymentOptions->getConfig()->getCards()->getClickToPay(),
                'one_click_card' => $this->configPaymentOptions->getConfig()->getCards()->getOneClickCard(),
                'conditions_to_approve' => $approveFinder->getConditionsToApproveForTemplate(),
                'selected' => $this->lastPaymentMethodCookie->getLastIdPayment() == $paymentMethod->getId(),
                'form_action' => $paymentMethod->getFrontUrl(),
            ]);
        $paymentOption->setAdditionalInformation($this->context->smarty->fetch('module:przelewy24payment/views/templates/front/payment_card_additional_information.tpl'));
    }

    protected function _createForm(PaymentOption $paymentOption, ?PaymentMethod $paymentMethod)
    {
    }
}
