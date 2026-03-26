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
use Przelewy24\Configuration\Enum\BlikMessageEnum;
use Przelewy24\Configuration\Enum\BlikStatusEnum;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24BlikTransaction;
use Przelewy24\Model\Dto\Przelewy24Transaction;
use Przelewy24\Model\Przlewy24AccountModel;

class Przelewy24paymentblikModuleFrontController extends ModuleFrontController
{
    private $sessionId;
    /**
     * @var Przelewy24BlikTransaction
     */
    private $blikTransaction;
    /**
     * @var Przelewy24Transaction
     */
    private $transaction;
    /**
     * @var false|mixed
     */
    private $sessionHash;

    public function postProcess()
    {
        $this->sessionHash = Tools::getValue('session_hash', false);
        $this->sessionId = (string) Przlewy24AccountModel::getSessionByHash($this->sessionHash);
        $this->blikTransaction = Przlewy24AccountModel::getBlikTransaction($this->sessionId);
        $this->transaction = Przlewy24AccountModel::getTransaction($this->sessionId);
        if (!$this->ajax) {
            exit;
        }
    }

    public function displayAjaxCheckStatus()
    {
        $try = Tools::getValue('try', '1');

        $this->returnJson($this->checkTransaction($try));
    }

    private function _getStatuses($status, $nextTry = 1)
    {
        return [
            'status' => $status,
            'completed' => (bool) ($status != ModuleConfiguration::PENDING || $nextTry > ModuleConfiguration::MAX_BLIK_CHECK_TRY),
            'nextTry' => $nextTry,
        ];
    }

    private function checkTransaction($try)
    {
        $messageEnum = $this->get(BlikMessageEnum::class);
        $statusEnum = $this->get(BlikStatusEnum::class);
        $urHelper = $this->get(UrlHelper::class);

        if (empty($this->blikTransaction->getSessionId()) && (empty($this->transaction->getReceived()) || empty($this->transaction->getPsIdOrder()))) {
            return $this->_getStatuses(ModuleConfiguration::PENDING, ++$try);
        } elseif (!empty($this->blikTransaction->getSessionId())) {
            $status = $statusEnum->isAuthorized($this->blikTransaction->getStatus()) && $this->blikTransaction->getError() == '0' ?
                ModuleConfiguration::SUCCESS :
                ModuleConfiguration::ERROR;
            $message = $messageEnum->getMessage($this->blikTransaction->getMessage());

            return array_merge($this->_getStatuses($status, ++$try), ['message' => $message, 'url_return' => $urHelper->getUrlReturn($this->sessionHash)]);
        } elseif (!empty($this->transaction->getReceived()) && !empty($this->transaction->getPsIdOrder())) {
            $message = $messageEnum->getMessage(BlikMessageEnum::CORRECT_TRANSACTION);

            return array_merge($this->_getStatuses(ModuleConfiguration::SUCCESS, ++$try), ['message' => $message, 'url_return' => $urHelper->getUrlReturn($this->blikTransaction->getSessionId())]);
        }
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
