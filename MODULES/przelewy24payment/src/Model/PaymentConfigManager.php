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

namespace Przelewy24\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Model\Dto\PaymentConfig;

class PaymentConfigManager
{
    public function manageOneClick(Przlewy24AccountModel $model, PaymentConfig $paymentConfig, bool $newOneClick, bool $oldOneClick, $paymentType)
    {
        $paymentExist = $paymentConfig->getPaymentMethodSeparateList()->getPaymentMethodByType($paymentType);
        if ($newOneClick && !$paymentExist) {
            $payment = $paymentConfig->getPaymentMethodNameList()->getPaymentMethodByType($paymentType);
            $this->addPayment($payment, $model, $paymentConfig);
        }
        if (!$newOneClick && $paymentExist && $oldOneClick) {
            $this->removeTypePayment($paymentType, $model, $paymentConfig);
        }
    }

    public function addPayment(?PaymentMethod $payment, Przlewy24AccountModel $model, PaymentConfig $paymentConfig)
    {
        if ($payment) {
            $paymentConfig->getPaymentMethodSeparateList()->add($payment);
            $model->savePaymentMethodSeparate($paymentConfig->getPaymentMethodSeparateList());
            \Db::getInstance()->insert('przelewy24_payment_config',
                [
                    'id_account' => $paymentConfig->getIdAccount(),
                    'payment_method_separate' => 1,
                ],
                false,
                true,
                \Db::ON_DUPLICATE_KEY
            );
        }
    }

    public function removePayment(PaymentMethod $payment, Przlewy24AccountModel $model, PaymentConfig $paymentConfig)
    {
        $paymentConfig->getPaymentMethodSeparateList()->removeIdPayment($payment->getId());
        $model->savePaymentMethodSeparate($paymentConfig->getPaymentMethodSeparateList());
    }

    public function removeTypePayment(string $paymentType, Przlewy24AccountModel $model, PaymentConfig $paymentConfig)
    {
        $paymentConfig->getPaymentMethodSeparateList()->removeTypePayment($paymentType);
        $model->savePaymentMethodSeparate($paymentConfig->getPaymentMethodSeparateList());
    }

    public function addPaymentCollection(PaymentMethodCollection $paymentMethodToAdd, PaymentConfig $paymentConfig, Przlewy24AccountModel $model)
    {
        $paymentMethodToAdd = $paymentMethodToAdd->diffCollection($paymentConfig->getPaymentMethodSeparateList());
        if ($paymentMethodToAdd->count() <= 0) {
            return;
        }

        foreach ($paymentMethodToAdd as $paymentMethod) {
            $paymentConfig->getPaymentMethodSeparateList()->add($paymentMethod);
        }

        return $model->savePaymentMethodSeparate($paymentConfig->getPaymentMethodSeparateList());
    }

    public function addTypeToCollection(PaymentMethodCollection $paymentMethodCollection, string $paymentType, PaymentConfig $paymentConfig)
    {
        $payment = $paymentConfig->getPaymentMethodNameList()->getPaymentMethodByType($paymentType);
        if ($payment) {
            $paymentMethodCollection->add($payment);
        }
    }
}
