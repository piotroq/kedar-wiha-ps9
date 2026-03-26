{**
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
 *}

{assign var='selector_fixed' value=$selector|default:'przelewy24-card'}

<div class="przelewy24-card-white-label__wrapper js-{$selector_fixed}-white-label-wrapper hidden-xs-up" style="display: none">
    <div class="przelewy24-card-white-label__modal">
        <div class="przelewy24-card-white-label__loader-wrapper js-{$selector_fixed}-white-label-loader">
            <div class="przelewy24-loader przelewy24-loader__icon">
                <div class="przelewy24-loader__spinner"></div>
            </div>
            <span class="przelewy24-loader__message d-block text-xs-center">
                {l s='Processing payment in progress...' d='Modules.Przelewy24payment.Shop'}
            </span>
            <span class="przelewy24-loader__message d-block text-xs-center">
                {l s='This may take a moment. Please wait.' d='Modules.Przelewy24payment.Shop'}
            </span>
        </div>
        <div id="{$selector_fixed}-white-label" class="przelewy24-card-white-label__iframe-wrapper"></div>
    </div>
</div>
