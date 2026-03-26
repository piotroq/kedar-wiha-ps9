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
        class="js-przelewy24-blik-payment-input"
        type="hidden"
        value="true"
>

<div
        class="alert hidden-xs-up text-xs-center js-przelewy24-blik-notice-wrapper"
        role="alert"
        style="display: none"
>
    <span class="przelewy24-loader__icon js-przelewy24-notice-icon rtl-no-flip pt-0 mr-0">
        <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="currentColor">
            <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
        </svg>
    </span>
    <span class="js-przelewy24-notice-message d-block mb-0"></span>
</div>

{include file="module:przelewy24payment/views/templates/front/_partials/loaders/external_payment_loader.tpl" selector="przelewy24-blik"}

<div
        class="przelewy24-blik-pay__submit-wrapper js-przelewy24-blik-wrapper mb-2"
        {if !empty($form_action)}data-url="{$form_action}"{/if}
>
    {include file="module:przelewy24payment/views/templates/front/_partials/blik/blik_code_input.tpl"}
    {include file="module:przelewy24payment/views/templates/front/_partials/blik/blik_regulations.tpl"}
    {include file="module:przelewy24payment/views/templates/front/payment_additional_information.tpl" showIntro=false}

    <div class="form-group">
        <button
                class="przelewy24-blik__submit-btn btn btn-primary js-przelewy24-blik-submit-button"
                type="submit"
                disabled="disabled"
        >
            {l s='Order with an obligation to pay' d='Modules.Przelewy24payment.Shop'}
        </button>
    </div>
</div>

{include file="module:przelewy24payment/views/templates/front/_partials/blik/blik_white_label.tpl" selector="przelewy24-blik"}
{include file="module:przelewy24payment/views/templates/front/_partials/notices/notices.tpl"}
