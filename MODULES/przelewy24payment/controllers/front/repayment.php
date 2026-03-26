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

use Przelewy24\Exceptions\RepaymentTimeException;
use Przelewy24\Helper\Image\ImageHelper;
use Przelewy24\Manager\PaymentOptions\PaymentOptionsManager;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Voter\LastTransactionTimeVoter;
use Przelewy24\Presenter\PaymentOptions\PaymentOptionsPresenter;

class Przelewy24paymentrepaymentModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $token = Tools::getValue('token');
        $idCart = Przlewy24AccountModel::getIdCartByToken($token);
        $cart = new Cart((int) $idCart);
        $paymentOptions = [];

        if (Przlewy24AccountModel::checkCartWasPayedByPrzelewy($idCart)) {
            $paymentOptionsManager = $this->getContainer()->get(PaymentOptionsManager::class);
            $paymentOptionsPresenter = new PaymentOptionsPresenter();
            $paymentOptions = $paymentOptionsPresenter->present($paymentOptionsManager->getPaymentOptions($cart));
        } else {
            $this->errors[] = 'This cart was not payed by Przelewy24';
        }

        try {
            $voter = $this->getContainer()->get(LastTransactionTimeVoter::class);
            $voter->vote((int) $idCart);
        } catch (RepaymentTimeException $e) {
            $this->errors[] = $e->getFrontMessage();
            $paymentOptions = [];
        }

        $imageHelper = $this->getContainer()->get(ImageHelper::class);

        $this->context->smarty->assign([
            'payment_options' => $paymentOptions,
            'logo_url' => $imageHelper->createUrl('przelewy24-logo.svg'),
        ]);
        $this->setTemplate('module:przelewy24payment/views/templates/front/repayment.tpl');
    }
}
