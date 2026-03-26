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

namespace Przelewy24\Provider\Refund;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Refund;
use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRefund;
use Przelewy24\Configuration\Enum\StatusDriverEnum;
use Przelewy24\Factory\Session\SessionIdFactory;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Helper\Url\UrlHelper;

class RefundProvider
{
    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    /**
     * @var UrlHelper
     */
    private $urlHelper;
    /**
     * @var SessionIdFactory
     */
    private $sessionIdFactory;

    public function __construct(TransactionScopeFactory $transactionScopeFactory, UrlHelper $urlHelper, SessionIdFactory $sessionIdFactory)
    {
        $this->transactionScopeFactory = $transactionScopeFactory;
        $this->urlHelper = $urlHelper;
        $this->sessionIdFactory = $sessionIdFactory;
    }

    public function getTransactionRefund(string $session_id, int $amount, string $description)
    {
        $transactionScope = $this->transactionScopeFactory->factory($session_id);
        $refund = new Refund();
        $refund->setSessionId($session_id);
        $refund->setAmount($amount);
        $refund->setDescription($description);
        $refund->setOrderId((int) $transactionScope->getTransaction()->getP24IdOrder());

        $transactionRefund = new TransactionRefund();
        $transactionRefund->setRefundsUuid($this->sessionIdFactory->getVersionPrefix(false));
        $transactionRefund->setRefunds([$refund]);
        $transactionRefund->setUrlStatus($this->urlHelper->getUrlStatus(StatusDriverEnum::REFUND_STATUS));

        return $transactionRefund;
    }
}
