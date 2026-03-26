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
{extends file='page.tpl'}

{block name='page_content'}
    <input
            class="js-przelewy24-order-confirmation-input"
            data-url="{$check_url}"
            type="hidden"
            value="{$completed}"
    >

    <div class="przelewy24-repayment__header mb-2">
        <div class="text-xs-center mb-2">
            <a href="https://przelewy24.pl" target="_blank">
                <img
                        class="img-fluid"
                        src="{$logo_url}"
                        alt="{l s='Pay with Przelewy24' d='Modules.Przelewy24payment.Shop'}"
                        width="198"
                        height="102"
                >
            </a>
        </div>

        <h3 class="h1 text-xs-center">
            {l s='Przelewy24 payment transaction' d='Modules.Przelewy24payment.Shop'}
        </h3>

        <p class="text-xs-center mb-0">
            {l s='Please wait for the payment confirmation.' d='Modules.Przelewy24payment.Shop'}
        </p>
    </div>

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/pending.tpl" visible=true}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/success.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/warning.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/warning_repayment.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/danger.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/long_term.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/cancelled.tpl"}

    {include file="modules/przelewy24payment/views/templates/front/_partials/alerts/already_payed.tpl"}

    {block name='hook_order_confirmation'}
        {hook h='displayOrderConfirmation' order=$order}
    {/block}
{/block}

