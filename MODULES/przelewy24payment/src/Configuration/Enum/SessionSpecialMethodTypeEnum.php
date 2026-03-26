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
class SessionSpecialMethodTypeEnum extends AbstractEnum
{
    public const BLIK_LVL0 = 'b0';

    public const BLIK_ONE_CLICK = 'b0oc';

    public const BLIK_RECURRENT = 'brec';
    public const CARD_IN_STORE = 'cc';
    public const CARD_IN_STORE_ONE_CLICK = 'ccoc';
    public const CARD_RECURRENT = 'ccrec';
    public const CARD_IN_STORE_CLICK_TO_PAY = 'ccc2p';
    public const GOOGLE_PAY_IN_STORE = 'gp';
}
