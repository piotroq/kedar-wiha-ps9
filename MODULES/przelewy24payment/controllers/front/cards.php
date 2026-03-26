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
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przelewy24CardModel;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Dto\AfterPaymentAction;
use Przelewy24\Payment\Payment\CardPayment;
use Przelewy24\Resolver\Cards\CardsLogoResolver;

class Przelewy24paymentcardsModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->checkCardIsEnabled();
        if (!$this->ajax) {
            if (Tools::isSubmit('removeCard')) {
                $this->removeCard();
            }
            if (Tools::isSubmit('setDefaultCard')) {
                $this->setDefaultCard();
            }
            if (Tools::isSubmit('show_info')) {
                $this->info[] = $this->trans('The request to add a card has been sent. Your card will appear after correct verification.', [], 'Modules.Przelewy24payment.Cards');
            }
            $this->presentCards();

            $this->setTemplate('module:przelewy24payment/views/templates/front/cards.tpl');
        }
    }

    public function displayAjaxAddCard()
    {
        try {
            $afterPaymentAction = null;
            $cart = $this->context->cart;
            $cart->save();
            $payment = $this->getContainer()->get(CardPayment::class);
            $config = $this->getContainer()->get(Przelewy24Config::class);
            $payment->setCart($cart);
            $account = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($cart->id_currency, $cart->id_shop);
            $config->setAccount($account, false);
            $payment->setConfig($config);
            $payment->createConnection();
            $payment->setExtraParams(Tools::getAllValues());
            $afterPaymentAction = $payment->initialPayment();
        } catch (FrontMessageException $fe) {
            $this->errors[] = $fe->getFrontMessage();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return $this->_returnResponseAjax($afterPaymentAction);
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

    private function removeCard()
    {
        $refId = Tools::getValue('refId');
        $model = Przelewy24CardModel::getCardByRefID($refId);

        if ($model->id_customer != $this->context->customer->id) {
            $this->errors[] = $this->trans('You cant remove this card', [], 'Modules.Przelewy24payment.Cards');

            return $this->redirectWithNotifications($this->context->link->getModuleLink('przelewy24payment', 'cards'));
        }

        if ($model->delete()) {
            $this->success[] = $this->trans('You removed card', [], 'Modules.Przelewy24payment.Cards');
        } else {
            $this->errors[] = $this->trans('Error during removing card', [], 'Modules.Przelewy24payment.Cards');
        }

        return $this->redirectWithNotifications($this->context->link->getModuleLink('przelewy24payment', 'cards'));
    }

    private function setDefaultCard()
    {
        $refId = Tools::getValue('refId');
        $model = Przelewy24CardModel::getCardByRefID($refId);

        if ($model->id_customer != $this->context->customer->id) {
            $this->errors[] = $this->trans('You cant set default this card', [], 'Modules.Przelewy24payment.Cards');

            return $this->redirectWithNotifications($this->context->link->getModuleLink('przelewy24payment', 'cards'));
        }

        if ($model->markAsDefault()) {
            $this->success[] = $this->trans('You set default card', [], 'Modules.Przelewy24payment.Cards');
        } else {
            $this->errors[] = $this->trans('Error during setting default card', [], 'Modules.Przelewy24payment.Cards');
        }

        return $this->redirectWithNotifications($this->context->link->getModuleLink('przelewy24payment', 'cards'));
    }

    private function presentCards()
    {
        $cardsLogoResolver = $this->getContainer()->get(CardsLogoResolver::class);
        $cards = Przelewy24CardModel::getCardsByIdCustomer($this->context->customer->id);
        $cardsLogoResolver->resolve($cards);
        $urlHelper = $this->getContainer()->get(UrlHelper::class);
        $this->context->smarty->assign(
            [
                'cards' => $cards,
                'regulations_link' => $urlHelper->getRegulationsUrl($this->context->language->iso_code),
                'information_link' => $urlHelper->getInformationGdprUrl($this->context->language->iso_code),
            ]
        );
    }

    private function checkCardIsEnabled()
    {
        try {
            $cart = $this->context->cart;
            $config = $this->getContainer()->get(Przelewy24Config::class);
            $account = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($cart->id_currency, $cart->id_shop);
            $config->setAccount($account, false);
            if (!$config->getCards()->getOneClickCard()) {
                Tools::redirect('index.php?controller=index');
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            Tools::redirect('index.php?controller=index');
        }
    }
}
