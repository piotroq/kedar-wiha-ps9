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

namespace Przelewy24\Payment\Voter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Exceptions\CartIsCanceledException;
use Przelewy24\Exceptions\CartIsPayedException;
use Przelewy24\Exceptions\CartNotBelongToCustomerException;
use Przelewy24\Exceptions\PaymentMethodNotAvailableException;
use Przelewy24\Exceptions\PaymentMethodNotConfiguredException;
use Przelewy24\Exceptions\PaymentMethodWrongIdPaymentException;
use Przelewy24\Factory\PaymentMethod\PaymentMethodCollectionFactory;
use Przelewy24\Payment\Checker\CartTransactionChecker;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;
use Przelewy24\Translator\Adapter\Translator;

class PaymentVoter
{
    /**
     * @var Przelewy24PaymentInterface
     */
    private $payment;

    /**
     * @var PaymentMethodCollectionFactory
     */
    private $paymentMethodsFactory;

    /**
     * @var CartTransactionChecker
     */
    private $cartTransactionChecker;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(
        PaymentMethodCollectionFactory $paymentMethodsFactory,
        CartTransactionChecker $cartTransactionChecker,
        Translator $translator
    ) {
        $this->paymentMethodsFactory = $paymentMethodsFactory;
        $this->cartTransactionChecker = $cartTransactionChecker;
        $this->translator = $translator;
    }

    public function vote(Przelewy24PaymentInterface $payment)
    {
        $this->payment = $payment;
        $this->_voteConfigured();
        $this->_voteCart();
        $this->_votePaymentMethodAvailable();
    }

    private function _voteConfigured()
    {
        if (!($this->payment->getCart() instanceof \Cart)) {
            throw new PaymentMethodNotConfiguredException('Cart not exist', $this->translator->trans('Cart is not configured.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if (!$this->payment->getCart()->id) {
            throw new PaymentMethodNotConfiguredException('Cart doesnt have id.', $this->translator->trans('Cart is not configured.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if (empty($this->payment->getCart()->getProducts())) {
            throw new PaymentMethodNotConfiguredException('Cart doesnt have products.', $this->translator->trans('Cart is not configured.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if (empty($this->payment->getConfig()->getAccount())) {
            throw new PaymentMethodNotConfiguredException('Account is not configured.', $this->translator->trans('Account is not configured.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if (!\Validate::isLoadedObject($this->payment->getConfig()->getAccount())) {
            throw new PaymentMethodNotConfiguredException('Account is not configured.', $this->translator->trans('Account is not configured.', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    private function _votePaymentMethodAvailable()
    {
        $extraParams = $this->payment->getExtraParams();

        if (!isset($extraParams['id_payment']) && $this->payment->getId() !== 0) {
            throw new PaymentMethodWrongIdPaymentException('Wrong payment method id.', $this->translator->trans('Wrong payment method id.', [], 'Modules.Przelewy24payment.Exception'));
        }

        if (
            isset($extraParams['id_payment'])
            && ($extraParams['id_payment'] != $this->payment->getId() && $this->payment->getId() !== 0)) {
            throw new PaymentMethodWrongIdPaymentException('Wrong payment method id.', $this->translator->trans('Wrong payment method id.', [], 'Modules.Przelewy24payment.Exception'));
        }

        if (isset($extraParams['id_payment']) && $extraParams['id_payment'] != 0) {
            $total = $this->payment->getCart()->getOrderTotal();
            $amount = (int) round($total * 100);

            $paymentMethodsCollection = $this->paymentMethodsFactory->factory(
                [
                    'account' => $this->payment->getConfig()->getAccount(),
                    'currency' => $this->payment->getConfig()->getAccount()->getIsoCurrency(),
                    'amount' => $amount,
                ]);
            $selectedPaymentMethod = $paymentMethodsCollection->intersectByIds([$extraParams['id_payment']]);
            if ($selectedPaymentMethod->count() != 1) {
                throw new PaymentMethodNotAvailableException('Payment method not available.', $this->translator->trans('Payment method not available.', [], 'Modules.Przelewy24payment.Exception'));
            }
            if ($selectedPaymentMethod->current()->getStatus() != true) {
                throw new PaymentMethodNotAvailableException('Payment method not available.', $this->translator->trans('Payment method not available.', [], 'Modules.Przelewy24payment.Exception'));
            }
        }
    }

    private function _voteCart()
    {
        if (
            (!\Context::getContext()->customer->is_guest && \Context::getContext()->customer->id !== null)
            && $this->payment->getCart()->id_customer != \Context::getContext()->customer->id) {
            throw new CartNotBelongToCustomerException('Customer is not owner Cart.', $this->translator->trans('Customer is not owner Cart.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if ($this->cartTransactionChecker->checkCartIsPayed($this->payment->getCart())) {
            throw new CartIsPayedException('Cart is already payed.', $this->translator->trans('Cart is already payed.', [], 'Modules.Przelewy24payment.Exception'));
        }
        if ($this->cartTransactionChecker->checkOrderIsPayedOrCanceled($this->payment->getCart())) {
            throw new CartIsCanceledException('Cart is canceled.', $this->translator->trans('Cart is canceled.', [], 'Modules.Przelewy24payment.Exception'));
        }
    }
}
