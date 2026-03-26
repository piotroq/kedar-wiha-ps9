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
<div
        class="przelewy24-card-tokenizer__wrapper js-przelewy24-card-tokenizer-wrapper {if !empty($cards) && $cards->count() > 0}hidden-xs-up{/if}"
        {if !empty($cards) && $cards->count() > 0}style="display: none" {/if}
>
    <div
            id="przelewy24-card-tokenizer"
            class="przelewy24-card-tokenizer js-przelewy24-card-tokenizer"
    >
        {if !empty($cards) && $cards->count() > 0}
            <p class="mb-0">
                <button class="przelewy24-card-tokenizer__back btn btn-sm btn-link js-przelewy24-card-tokenizer-back-button">
                    {l s='Back to saved cards' d='Modules.Przelewy24payment.Shop'}
                </button>
            </p>
        {/if}
    </div>

    <div
            class="przelewy24-card-tokenizer__submit-wrapper my-2 js-przelewy24-card-submit-wrapper"
            {if !empty($form_action)}data-url="{$form_action}"{/if}
    >
        <div class="przelewy24-card-tokenizer__agreements js-przelewy24-card-regulations-wrapper">
            <div class="form-group przelewy24-checkbox-group">
                <input
                        class="przelewy24-checkbox__input js-przelewy24-regulation-tokenizer"
                        id="p24_regulation_accept_tokenizer"
                        name="p24_regulation_accept"
                        type="checkbox"
                        value="1"
                >
                <label class="przelewy24-checkbox__label" for="p24_regulation_accept_tokenizer">
                    {{l
                    s='I declare that I have read the [1]regulations[/1] and [2]information obligation[/2] of the Przelewy24 service'
                    sprintf=[
                    '[1]' => '<a href="'|cat:$regulations_link|cat:'" target="_blank">',
                    '[/1]' => '</a>',
                    '[2]' => '<a href="'|cat:$information_link|cat:'" target="_blank">',
                    '[/2]' => '</a>'
                    ]
                    d='Modules.Przelewy24payment.Shop'
                    }|p_24_html_entity_decode|cleanHtml nofilter}
                </label>
            </div>
            {if !empty($conditions_to_approve) && $conditions_to_approve|count}
                <p class="ps-hidden-by-js">
                    {* At the moment, we're not showing the checkboxes when JS is disabled
                       because it makes ensuring they were checked very tricky and overcomplicates
                       the template. Might change later.
                    *}
                    {l s='By confirming the order, you certify that you have read and agree with all of the conditions below:' d='Shop.Theme.Checkout'}
                </p>

                {foreach $conditions_to_approve as $condition_name => $condition}
                    <div class="form-group przelewy24-checkbox-group">
                        <input
                                class="przelewy24-checkbox__input ps-shown-by-js"
                                id="conditions_to_approve_tokenizer[{$condition_name}]"
                                name="conditions_to_approve[{$condition_name}]"
                                required
                                type="checkbox"
                                value="1"
                        >
                        <label class="przelewy24-checkbox__label js-terms" for="conditions_to_approve_tokenizer[{$condition_name}]">
                            {$condition|cleanHtml nofilter}
                        </label>
                    </div>
                {/foreach}
            {/if}
        </div>

        {include file="module:przelewy24payment/views/templates/front/payment_additional_information.tpl" showIntro=false}

        {if !empty($one_click_card)}
            <button
                    class="przelewy24-loader-button btn btn-primary js-przelewy24-card-tokenizer-submit-button mr-1"
                    data-save-one-click="true"
                    type="submit"
                    disabled="disabled"
            >
                <div class="przelewy24-loader przelewy24-loader-button__loader">
                    <div class="przelewy24-loader__spinner"></div>
                </div>
                {l s='Save card and pay' d='Modules.Przelewy24payment.Shop'}
            </button>
        {/if}

        <button
                class="przelewy24-loader-button btn btn-sm btn-secondary js-przelewy24-card-tokenizer-submit-button"
                type="submit"
                disabled="disabled"
        >
            <div class="przelewy24-loader przelewy24-loader-button__loader">
                <div class="przelewy24-loader__spinner"></div>
            </div>
            {l s='Pay (temporary)' d='Modules.Przelewy24payment.Shop'}
        </button>
    </div>
</div>
