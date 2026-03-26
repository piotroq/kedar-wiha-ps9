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
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Saved cards' d='Modules.Przelewy24payment.Shop'}
{/block}

{block name='page_content'}
    <div class="card card-block">
        <input
                class="js-przelewy24-card-payment-input"
                type="hidden"
                value="true"
        >

        <div
                class="alert hidden-xs-up text-xs-center js-przelewy24-card-notice-wrapper"
                role="alert"
                style="display: none"
        >
            <span class="js-przelewy24-notice-message d-block mb-0"></span>
        </div>

        {include file="module:przelewy24payment/views/templates/front/_partials/cards/saved_cards.tpl" cards=$cards}

        {include file="module:przelewy24payment/views/templates/front/_partials/cards/save_card_tokenizer.tpl"}

        {include file="module:przelewy24payment/views/templates/front/_partials/cards/card_white_label.tpl" selector="przelewy24-card"}
    </div>
{/block}
