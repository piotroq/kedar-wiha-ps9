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

namespace Przelewy24\Payment\Checker;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\QueryParameters\TransactionBySessionQueryParameters;
use Przelewy24\Api\Przelewy24\Request\TransactionBySessionRequest;
use Przelewy24\Dto\TransactionBySession;
use Przelewy24\Dto\TransactionScope;
use Przelewy24\Exceptions\WrongTransactionBySessionRequestException;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Helper\Style\StyleHelper;

class TransactionStatusChecker
{
    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    /**
     * @var TransactionScope
     */
    private $transactionScope;

    private $response;

    /**
     * @var object
     */
    private $transactionBySession;

    public function __construct(TransactionScopeFactory $transactionScopeFactory)
    {
        $this->transactionScopeFactory = $transactionScopeFactory;
    }

    public function check($session_id)
    {
        $this->transactionScope = $this->transactionScopeFactory->factory($session_id);
        $this->_sendRequest();
        $this->_checkResponse();
        $this->_createTransactionBySessionObject();

        return (bool) in_array($this->transactionBySession->getStatus(), [1, 2]);
    }

    private function _sendRequest()
    {
        $body = new TransactionBySessionQueryParameters();
        $body->setSessionId($this->transactionScope->getSessionId());

        $request = new TransactionBySessionRequest($body);
        $this->response = $this->transactionScope->getConnection()->sendRequest($request);
    }

    private function _checkResponse()
    {
        if ($this->response->getStatus() != 200) {
            throw new WrongTransactionBySessionRequestException('Wrong status code: ' . $this->response->getStatus());
        }
    }

    private function _createTransactionBySessionObject()
    {
        $this->transactionBySession = StyleHelper::fillObject(new TransactionBySession(), $this->response->getData());
    }

    public function getLastResponse()
    {
        return $this->transactionBySession;
    }
}
