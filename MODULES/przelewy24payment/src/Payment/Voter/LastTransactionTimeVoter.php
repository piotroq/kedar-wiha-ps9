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

namespace Przelewy24\Payment\Voter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Exceptions\RepaymentTimeException;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Translator\Adapter\Translator;

class LastTransactionTimeVoter
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function vote($idCart)
    {
        $lastTransaction = Przlewy24AccountModel::getLastTransactionByCart($idCart);
        if (empty($lastTransaction)) {
            return true;
        }

        $this->_voteLastTransaction($lastTransaction);
    }

    private function _voteLastTransaction($lastTransaction)
    {
        if (in_array($lastTransaction['id_payment'], ModuleConfiguration::LONG_TERM_PAYMENT_ID)) {
            throw new RepaymentTimeException('Cannot repay this transaction', $this->translator->trans('Cannot repay this transaction', [], 'Modules.Przelewy24payment.Exception'));
        }

        $hourToRepayment = ModuleConfiguration::SHORT_TERM_REPAYMENT_HOUR;
        $transactionDate = new \DateTime($lastTransaction['date_add']);
        $today = new \DateTime();
        $transactionDate->modify('+' . $hourToRepayment . ' hour');

        if ($transactionDate > $today) {
            throw new RepaymentTimeException('You must wait to: ' . $transactionDate->format('Y-m-d H:i:s') . ' to repay this transaction', $this->translator->trans('You must wait to: %date% to repay this transaction', ['%date%' => $transactionDate->format('Y-m-d H:i:s')], 'Modules.Przelewy24payment.Exception'));
        }
    }
}
