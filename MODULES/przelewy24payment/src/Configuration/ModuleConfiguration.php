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

namespace Przelewy24\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleConfiguration
{
    public const MODULE_NAME = 'przelewy24payment';
    public const PRZELEWY24 = 'przelewy24';

    public const TRANSLATION_PATH = _PS_MODULE_DIR_ . self::MODULE_NAME . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR;

    public const CONFIG_PATH = _PS_MODULE_DIR_ . self::MODULE_NAME . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

    public const DATABASE_YML_FILE = self::CONFIG_PATH . DIRECTORY_SEPARATOR . 'database.yml';

    public const HOOKS_YML_FILE = self::CONFIG_PATH . DIRECTORY_SEPARATOR . 'hooks.yml';

    public const SUCCESS = 'success';

    public const PENDING = 'pending';

    public const LONG_TERM_PAYMENT = 'long_term_payment';

    public const ERROR = 'error';

    public const MAX_CHECK_TRY = 5;
    public const MAX_BLIK_CHECK_TRY = 36;

    public const LONG_TERM_PAYMENT_ID = [303];

    public const IMG_PATH = 'views/img';

    public const CARD_ID_PAYMENT = 242;

    public const GOOGLE_ID_PAYMENT = 265;

    public const APPLE_ID_PAYMENT = 252;

    public const SHORT_TERM_REPAYMENT_HOUR = 1;

    public const EXCLUDED_PYMENT = [self::BLIK_LEVEL_O_ID_PAYMENT];

    public const CALCULATOR_ID_PAYMENT = 303;
    public const BLIK_LEVEL_O_ID_PAYMENT = 181;

    public const MAX_PRODUCT_CALCULATOR_PRICE = 50000;
    public const MIN_PRODUCT_CALCULATOR_PRICE = 100;
    public const CALCULATOR_CURRENCY = 'PLN';
    public const CALCULATOR_CMS = 'presta';
}
