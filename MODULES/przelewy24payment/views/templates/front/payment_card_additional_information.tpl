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
<input
        class="js-przelewy24-one-click-method-input"
        type="hidden"
        value="true"
>

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

{if !empty($cards) && $cards->count() > 0}
    {include file="module:przelewy24payment/views/templates/front/_partials/cards/card_one_click.tpl" cards=$cards}
{/if}

{include file="module:przelewy24payment/views/templates/front/_partials/cards/card_tokenizer.tpl"}

{include file="module:przelewy24payment/views/templates/front/_partials/cards/card_white_label.tpl" selector="przelewy24-card"}

{include file="module:przelewy24payment/views/templates/front/_partials/notices/notices.tpl"}
