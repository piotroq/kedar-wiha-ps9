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
if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Factory\Payment\PaymentFactory;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Dto\AfterPaymentAction;

class Przelewy24paymentpaymentModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $afterPaymentAction = null;
            $id_payment = (int) Tools::getValue('id_payment', 0);
            $id_cart = (int) Tools::getValue('id_cart', 0);
            $cart = $this->context->cart;
            if ($id_cart) {
                $cart = new Cart((int) $id_cart);
            }
            $paymentFactory = $this->getContainer()->get(PaymentFactory::class);
            $account = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($cart->id_currency, $cart->id_shop);
            $payment = $paymentFactory->factory($id_payment, $account);
            $payment->setCart($cart);
            $payment->createConnection();
            //            $payment->setAccount($account);
            $payment->setExtraParams(Tools::getAllValues());
            $payment->validatePaymentMethod();
            $afterPaymentAction = $payment->pay();
            $this->errors = $afterPaymentAction->getErrors();
        } catch (FrontMessageException $fe) {
            $this->errors[] = $fe->getFrontMessage();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        if ($this->ajax) {
            return $this->_returnResponseAjax($afterPaymentAction);
        }

        return $this->_returnResponse($afterPaymentAction);
    }

    private function _returnResponseAjax(AfterPaymentAction $afterPaymentAction = null)
    {
        if (!empty($this->errors)) {
            $data['errors'] = $this->errors;
        } else {
            $data = $afterPaymentAction->getParams();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function _returnResponse(AfterPaymentAction $afterPaymentAction = null)
    {
        if ($this->errors) {
            $this->redirectWithNotifications('index.php?controller=order&step=1');
        } else {
            Tools::redirect($afterPaymentAction->getRedirect());
        }
        exit;
    }
}
