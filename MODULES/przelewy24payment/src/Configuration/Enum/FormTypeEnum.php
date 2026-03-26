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

use Przelewy24\Form\Type\AppleType;
use Przelewy24\Form\Type\BlikType;
use Przelewy24\Form\Type\CardsType;
use Przelewy24\Form\Type\CredentialsType;
use Przelewy24\Form\Type\ExtraChargeType;
use Przelewy24\Form\Type\GoogleType;
use Przelewy24\Form\Type\InstallmentsType;
use Przelewy24\Form\Type\OrderType;
use Przelewy24\Form\Type\PaymentType;
use Przelewy24\Form\Type\StateType;
use Przelewy24\Form\Type\TimeConfigType;
use Przelewy24\Translator\Adapter\Translator;

class FormTypeEnum
{
    public const CREDENTIALS = 'credentials';

    public const EXTRA_CHARGE = 'extra-charge';

    public const ORDER = 'order';

    public const INSTALLMENTS = 'installments';

    public const PAYMENT = 'payment';

    public const CARDS = 'cards';

    public const BLIK = 'blik';

    public const GOOGLE = 'google';

    public const APPLE = 'apple';

    public const STATE = 'state';

    public const TIME = 'time';

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public static function getFormTYpe($type)
    {
        switch ($type) {
            case self::CREDENTIALS:
                return CredentialsType::class;
            case self::EXTRA_CHARGE:
                return ExtraChargeType::class;
            case self::ORDER:
                return OrderType::class;
            case self::INSTALLMENTS:
                return InstallmentsType::class;
            case self::PAYMENT:
                return PaymentType::class;
            case self::STATE:
                return StateType::class;
            case self::TIME:
                return TimeConfigType::class;
            case self::APPLE:
                return AppleType::class;
            case self::GOOGLE:
                return GoogleType::class;
            case self::CARDS:
                return CardsType::class;
            case self::BLIK:
                return BlikType::class;
        }
    }

    public function getTabName($type)
    {
        switch ($type) {
            case self::CREDENTIALS:
                return $this->translator->trans('Credentials', [], 'Modules.Przelewy24payment.Tab');
            case self::EXTRA_CHARGE:
                return $this->translator->trans('Extra Charge', [], 'Modules.Przelewy24payment.Tab');
            case self::ORDER:
                return $this->translator->trans('Order', [], 'Modules.Przelewy24payment.Tab');
            case self::INSTALLMENTS:
                return $this->translator->trans('Installments', [], 'Modules.Przelewy24payment.Tab');
            case self::PAYMENT:
                return $this->translator->trans('Payment', [], 'Modules.Przelewy24payment.Tab');
            case self::CARDS:
                return $this->translator->trans('Cards', [], 'Modules.Przelewy24payment.Tab');
            case self::APPLE:
                return $this->translator->trans('Apple Pay', [], 'Modules.Przelewy24payment.Tab');
            case self::GOOGLE:
                return $this->translator->trans('Google Pay', [], 'Modules.Przelewy24payment.Tab');
            case self::STATE:
                return $this->translator->trans('State', [], 'Modules.Przelewy24payment.Tab');
            case self::TIME:
                return $this->translator->trans('Processing Time', [], 'Modules.Przelewy24payment.Tab');
            case self::BLIK:
                return $this->translator->trans('Blik', [], 'Modules.Przelewy24payment.Tab');
        }
    }

    public function isRedirect($type)
    {
        switch ($type) {
            case self::CREDENTIALS:
            case self::BLIK:
            case self::PAYMENT:
            case self::APPLE:
            case self::GOOGLE:
            case self::CARDS:
                return true;
            default:
                return false;
        }
    }

    public static function getIdByType($type)
    {
        switch ($type) {
            case self::CREDENTIALS:
                return 1;
            case self::EXTRA_CHARGE:
                return 11;
            case self::ORDER:
                return 3;
            case self::INSTALLMENTS:
                return 4;
            case self::PAYMENT:
                return 5;
            case self::CARDS:
                return 7;
            case self::APPLE:
                return 9;
            case self::GOOGLE:
                return 8;
            case self::STATE:
                return 2;
            case self::TIME:
                return 10;
            case self::BLIK:
                return 6;
        }
    }
}
