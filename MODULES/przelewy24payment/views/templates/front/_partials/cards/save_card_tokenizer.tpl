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
        class="przelewy24-card-tokenizer__wrapper js-przelewy24-card-tokenizer-wrapper hidden-xs-up"
        style="display: none"
>
    <div
            id="przelewy24-card-tokenizer"
            class="przelewy24-card-tokenizer js-przelewy24-card-tokenizer"
    >
        <h3 class="przelewy24-card-tokenizer__header">
            {l s='Add new card' d='Modules.Przelewy24payment.Shop'}
        </h3>
    </div>

    <div
            class="przelewy24-card-tokenizer__submit-wrapper przelewy24-card-tokenizer__submit-wrapper--account my-2 js-przelewy24-card-submit-wrapper"
            data-url="{url entity='module' name='przelewy24payment' controller='cards' params=['action' => 'addCard']}"
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
        </div>
        <button
                class="przelewy24-loader-button btn btn-primary js-przelewy24-card-tokenizer-submit-button mr-1"
                data-save-one-click="true"
                type="submit"
                disabled="disabled"
        >
            <div class="przelewy24-loader przelewy24-loader-button__loader">
                <div class="przelewy24-loader__spinner"></div>
            </div>
            {l s='Save card' d='Modules.Przelewy24payment.Shop'}
        </button>
        <button class="przelewy24-card-tokenizer__back btn btn-sm btn-link js-przelewy24-card-tokenizer-back-button">
            {l s='Back to saved cards' d='Modules.Przelewy24payment.Shop'}
        </button>
    </div>
</div>
