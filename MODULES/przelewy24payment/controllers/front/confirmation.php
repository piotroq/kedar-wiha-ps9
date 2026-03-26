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

use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Handler\Transaction\PaymentConfirmHandler;
use Przelewy24\Helper\Image\ImageHelper;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Payment\Checker\TransactionStatusChecker;
use Przelewy24\Payment\Voter\TransactionAcceptedVoter;

class Przelewy24paymentconfirmationModuleFrontController extends ModuleFrontController
{
    private $sessionId;
    private $transaction;

    public function postProcess()
    {
        $sessionHash = Tools::getValue('session_hash', false);
        $this->transaction = Przlewy24AccountModel::getTransactionByHash($sessionHash);
        $this->sessionId = $this->transaction->getSessionId();
        if (!$this->ajax) {
            $this->context->smarty->assign($this->getIndexData());
            $this->setTemplate('module:przelewy24payment/views/templates/front/confirmation.tpl');
        }
    }

    public function displayAjaxCheckStatus()
    {
        $try = Tools::getValue('try', '1');
        $status = $this->checkTransaction();
        if ($try == ModuleConfiguration::MAX_CHECK_TRY && $status !== ModuleConfiguration::SUCCESS) {
            $status = $this->checkTransactionByApi();
        }
        $this->returnJson(array_merge($this->_getStatuses($status, ++$try), ['presented_order' => $this->getOrderPresent()]));
    }

    private function getIndexData()
    {
        $imageHelper = $this->getContainer()->get(ImageHelper::class);
        $urlHelper = $this->getContainer()->get(UrlHelper::class);
        $data = [
            'check_url' => $urlHelper->getCheckUrl($this->transaction->getSessionHash()),
            'repayment_url' => $urlHelper->getRepayUrl($this->transaction->getIdCart()),
            'logo_url' => $imageHelper->createUrl('przelewy24-logo.svg'),
            'presented_order' => $this->getOrderPresent(),
            'order' => $this->getOrder(),
        ];

        return array_merge($data, $this->_getStatuses($this->checkTransaction()));
    }

    private function checkTransaction()
    {
        try {
            $transactionAcceptedVoter = new TransactionAcceptedVoter();

            return $transactionAcceptedVoter->vote($this->sessionId) ? ModuleConfiguration::SUCCESS : ModuleConfiguration::PENDING;
        } catch (Exception $ex) {
            return ModuleConfiguration::ERROR;
        }
    }

    private function checkTransactionByApi()
    {
        try {
            $transactionChecker = $this->getContainer()->get(TransactionStatusChecker::class);
            if ($transactionChecker->check($this->sessionId)) {
                $paymentConfirmHandler = $this->getContainer()->get(PaymentConfirmHandler::class);
                $result = $paymentConfirmHandler->handle($transactionChecker->getLastResponse());

                return ($result == PaymentConfirmHandler::CONFIRM) ? ModuleConfiguration::SUCCESS : $result;
            }

            return in_array($this->transaction->getIdPayment(), ModuleConfiguration::LONG_TERM_PAYMENT_ID)
                ? ModuleConfiguration::LONG_TERM_PAYMENT
                : ModuleConfiguration::PENDING;
        } catch (Exception $ex) {
            return ModuleConfiguration::ERROR;
        }
    }

    private function _getStatuses($status, $nextTry = 1)
    {
        return [
            'status' => $status,
            'completed' => (bool) ($status == ModuleConfiguration::SUCCESS || $nextTry > ModuleConfiguration::MAX_CHECK_TRY),
            'nextTry' => $nextTry,
        ];
    }

    protected function getOrderPresent()
    {
        $id_order = Order::getIdByCartId($this->transaction->getIdCart());
        if (!empty($id_order)) {
            $order = new Order($id_order);
            $presenter = new OrderPresenter();

            return $presenter->present($order);
        }

        return null;
    }

    protected function getOrder()
    {
        $id_order = Order::getIdByCartId($this->transaction->getIdCart());
        if (!empty($id_order)) {
            $order = new Order($id_order);

            return $order;
        }

        return null;
    }

    private function returnJson($data)
    {
        ob_end_clean();
        header('Content-Type: application/json');
        json_encode($data);
        $this->ajaxRender(json_encode($data));
        exit;
    }
}
