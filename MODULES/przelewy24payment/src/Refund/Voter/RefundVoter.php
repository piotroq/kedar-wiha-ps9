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

namespace Przelewy24\Refund\Voter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Refund;
use Przelewy24\Exceptions\TransactionFullRefundedException;
use Przelewy24\Exceptions\WrongDataRefundException;
use Przelewy24\Model\Przelewy24RefundModel;
use Przelewy24\Translator\Adapter\Translator;

class RefundVoter
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function vote(Refund $refund)
    {
        $this->_voteTransactionRefunded($refund);
        $this->_voteValidData($refund);
    }

    public function _voteValidData($refund)
    {
        if (
            empty($refund->getDescription())
            || empty($refund->getSessionId())
            || empty($refund->getAmount())
            || empty($refund->getOrderId())
        ) {
            throw new WrongDataRefundException('Wrong data refund', $this->translator->trans('Wrong data refund', [], 'Modules.Przelewy24payment.Exception'));
        }

        if (Przelewy24RefundModel::getAllowedRefundAmount($refund->getSessionId()) < $refund->getAmount()) {
            throw new WrongDataRefundException('Amount cannot be greater than the transaction amount', $this->translator->trans('Amount cannot be greater than the transaction amount', [], 'Modules.Przelewy24payment.Exception'));
        }
    }

    public function _voteTransactionRefunded($refund)
    {
        if (Przelewy24RefundModel::isTransactionFullRefund($refund->getSessionId())) {
            throw new TransactionFullRefundedException('Transaction full refund', $this->translator->trans('Transaction full refund', [], 'Modules.Przelewy24payment.Exception'));
        }
    }
}
