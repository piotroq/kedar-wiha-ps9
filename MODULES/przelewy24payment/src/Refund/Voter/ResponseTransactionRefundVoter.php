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

use Przelewy24\Api\Przelewy24\Response\PrzelewyResposne;
use Przelewy24\Exceptions\RefundTransactionException;

class ResponseTransactionRefundVoter
{
    public function voteResult(PrzelewyResposne $resposne)
    {
        if ($resposne->getStatus() !== 201) {
            $error = $resposne->getError();
            if (!empty($error) && is_array($error) && isset($error[0]['message'])) {
                $error = $error[0]['message'];
            }
            $message = !empty($error) ? $error : 'Error when refunding transaction';
            throw new RefundTransactionException($message);
        }
    }
}
