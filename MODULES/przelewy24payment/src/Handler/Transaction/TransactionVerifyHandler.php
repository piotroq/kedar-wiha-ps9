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

namespace Przelewy24\Handler\Transaction;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\TransactionVerify;
use Przelewy24\Api\Przelewy24\Request\VerifyTransactionRequest;
use Przelewy24\Dto\Interfaces\TransactionVerifyFullDataInterface;
use Przelewy24\Dto\Interfaces\TransactionVerifyRequiredDataInterface;
use Przelewy24\Dto\TransactionScope;
use Przelewy24\Exceptions\VerifyTransactionFailException;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Translator\Adapter\Translator;

class TransactionVerifyHandler
{
    private $transactionData;

    private $response;

    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    /**
     * @var TransactionScope
     */
    private $transactionScope;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(TransactionScopeFactory $transactionScopeFactory, Translator $translator)
    {
        $this->transactionScopeFactory = $transactionScopeFactory;
        $this->translator = $translator;
    }

    public function handle(TransactionVerifyRequiredDataInterface $transactionData)
    {
        $this->transactionData = $transactionData;
        $this->transactionScope = $this->transactionScopeFactory->factory($this->transactionData->getSessionId());
        $this->_verifyTransactionRequest();
        $this->_validateVerifyTransactionResponse();

        return true;
    }

    private function _verifyTransactionRequest()
    {
        $transactionVerify = new TransactionVerify();
        $transactionVerify->setSessionId($this->transactionData->getSessionId());
        $transactionVerify->setCurrency($this->transactionData->getCurrency());
        $transactionVerify->setOrderId($this->transactionData->getOrderId());
        if ($this->transactionData instanceof TransactionVerifyFullDataInterface) {
            $transactionVerify->setAmount($this->transactionData->getAmount());
            $transactionVerify->setPosId($this->transactionData->getPosId());
            $transactionVerify->setMerchantId($this->transactionData->getMerchantId());
        } else {
            $transactionVerify->setAmount($this->transactionScope->getTransaction()->getAmount());
            $transactionVerify->setPosId($this->transactionScope->getTransaction()->getShopId());
            $transactionVerify->setMerchantId($this->transactionScope->getTransaction()->getMerchantId());
        }
        $transactionVerify->calculateSign($this->transactionScope->getConfig()->getCredentials()->getSalt());
        $request = new VerifyTransactionRequest($transactionVerify);
        $this->response = $this->transactionScope->getConnection()->sendRequest($request);
    }

    private function _validateVerifyTransactionResponse()
    {
        if ($this->response->getStatus() !== 200) {
            throw new VerifyTransactionFailException('Transaction verify failed', $this->translator->trans('Transaction verify failed', [], 'Modules.Przelewy24payment.Exception'));
        }
    }
}
