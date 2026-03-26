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

namespace Przelewy24\Factory\Payment;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Factory\Payment\Exceptions\WrongPaymentMethodException;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Payment\Interfaces\Przelewy24PaymentInterface;

class PaymentFactory
{
    private $paymentsCollection;
    private $config;

    public function __construct($paymentsCollection)
    {
        $this->paymentsCollection = $paymentsCollection;
    }

    public function factory(int $id_payment, Przlewy24AccountModel $accountModel): Przelewy24PaymentInterface
    {
        $this->config = null;
        $currentPayment = null;
        foreach ($this->paymentsCollection as $payment) {
            $this->_initializeConfig($accountModel, $payment->getConfig());
            $payment->setConfig($this->config);

            if (0 == $payment->getId() && $currentPayment === null) {
                $currentPayment = $payment;
            }
            if ($payment->isCurrentPayment($id_payment)) {
                $currentPayment = $payment;
            }
            //            if ($payment->getId() === $id_payment) {
            //                $currentPayment =  $payment;
            //            }
        }
        if (null === $currentPayment) {
            throw new WrongPaymentMethodException('Payment not found');
        }

        return $currentPayment;
    }

    private function _initializeConfig(Przlewy24AccountModel $accountModel, Przelewy24Config $config)
    {
        if (empty($this->config)) {
            $this->config = $config;
            $this->config->setAccount($accountModel);
        }
    }
}
