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

namespace Przelewy24\Configuration\Enum;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Translator\Adapter\Translator;

class BlikMessageEnum extends AbstractEnum
{
    public const CORRECT_TRANSACTION = 'Correct Transaction';
    public const ER_WRONG_TICKET = 'ER_WRONG_TICKET';
    public const ER_TIC_EXPIRED = 'ER_TIC_EXPIRED';
    public const ER_TIC_STS = 'ER_TIC_STS';
    public const ER_TIC_USED = 'ER_TIC_USED';
    public const INSUFFICIENT_FUND = 'INSUFFICIENT_FUND';
    public const LIMIT_EXCEEDED = 'LIMIT_EXCEEDED';
    public const USER_TIMEOUT = 'USER_TIMEOUT';
    public const TIMEOUT = 'TIMEOUT';
    public const AM_TIMEOUT = 'AM_TIMEOUT';
    public const ER_BAD_PIN = 'ER_BAD_PIN';
    public const USER_DECLINED = 'USER_DECLINED';
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getMessage($message)
    {
        switch ($message) {
            case self::CORRECT_TRANSACTION:
                return $this->translator->trans('Correct Transaction', [], 'Modules.Przelewy24payment.Blik');
            case self::ER_WRONG_TICKET:
            case self::ER_TIC_EXPIRED:
            case self::ER_TIC_STS:
            case self::ER_TIC_USED:
                return $this->translator->trans('Incorrect BLIK code was entered. Try again.', [], 'Modules.Przelewy24payment.Blik');
            case self::INSUFFICIENT_FUND:
            case self::LIMIT_EXCEEDED:
                return $this->translator->trans('Payment failed. Check the reason in the banking application and try again.', [], 'Modules.Przelewy24payment.Blik');
            case self::USER_TIMEOUT:
            case self::TIMEOUT:
            case self::AM_TIMEOUT:
                return $this->translator->trans('Payment failed - not confirmed on time in the banking application. Try again.', [], 'Modules.Przelewy24payment.Blik');
            case self::ER_BAD_PIN:
            case self::USER_DECLINED:
                return $this->translator->trans('Payment failed. Check the reason in the banking application and try again.', [], 'Modules.Przelewy24payment.Blik');
            default:
                return $this->translator->trans('Payment failed. Check the reason in the banking application and try again.', [], 'Modules.Przelewy24payment.Blik');
        }
    }
}
