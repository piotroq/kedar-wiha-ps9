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
{if !empty($cards) && $cards->count() > 0}
    {$isCardDefault = false}
    {foreach $cards as $card}
        {if !empty($card->getDefault())}
            {$isCardDefault = true}
            {break}
        {/if}
    {/foreach}

    <div class="przelewy24-saved-cards js-przelewy24-saved-cards-wrapper">
        <p class="przelewy24-saved-cards__title">
            {l s='Choose saved card' d='Modules.Przelewy24payment.Shop'}
        </p>
        <div class="przelewy24-saved-cards__list">
            {foreach $cards as $card}
                <div class="przelewy24-saved-cards__item">
                    <div class="przelewy24-saved-cards__content">
                        <input
                                class="przelewy24-saved-cards__input js-przelewy24-saved-cards-input"
                                type="radio"
                                {if !empty($card->getType())}
                                    data-card-type="{$card->getType()}"
                                {/if}
                                {if !empty($card->getIdCard())}
                                    data-card-id="{$card->getIdCard()}"
                                {/if}
                                {if !empty($card->getIdCard())}
                                    id="przelewy24-saved-cards-{$card->getIdCard()}"
                                {else}
                                    id="przelewy24-saved-cards-{$card@index}"
                                {/if}
                                name="przelewy24-saved-cards"
                                {if !empty($card->getRefId())}
                                    value="{$card->getRefId()}"
                                {else}
                                    value=""
                                {/if}
                                {if $isCardDefault && !empty($card->getDefault())}
                                    checked
                                {elseif !$isCardDefault && $card@index === 0}
                                    checked
                                {/if}
                        />
                        <label
                                class="przelewy24-saved-cards__label przelewy24-saved-cards__label--clickable d-block text-xs-left"
                                {if !empty($card->getIdCard())}
                                    for="przelewy24-saved-cards-{$card->getIdCard()}"
                                {else}
                                    for="przelewy24-saved-cards-{$card@index}"
                                {/if}
                        >
                            <div class="przelewy24-saved-cards__row">
                                {if !empty($card->getLogo())}
                                    <div class="przelewy24-saved-cards__col przelewy24-saved-cards__col--type text-xs-center">
                                        <img
                                                class="przelewy24-saved-cards__img img-fluid"
                                                loading="lazy"
                                                src="{$card->getLogo()}"
                                                alt="{$card->getType()}"
                                                title="{$card->getType()}"
                                        >
                                    </div>
                                {/if}
                                {if !empty($card->getMask())}
                                    <div class="przelewy24-saved-cards__col przelewy24-saved-cards__col--number">
                                        {if !empty($card->getType()) || !empty($card->getMask())}
                                            <div class="przelewy24-saved-cards__content">
                                                {if !empty($card->getMask())}
                                                    <span class="przelewy24-saved-cards__number">
                                                        {$card->getMask()}
                                                    </span>
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </label>
                    </div>
                </div>
            {/foreach}
        </div>

        <div
                class="przelewy24-saved-cards__submit-wrapper my-2 js-przelewy24-card-submit-wrapper"
                {if !empty($form_action)}data-url="{$form_action}"{/if}
        >
            <div class="przelewy24-saved-cards__agreements js-przelewy24-card-regulations-wrapper">
                <div class="form-group przelewy24-checkbox-group">
                    <input
                            class="przelewy24-checkbox__input js-przelewy24-regulation-one-click"
                            id="p24_regulation_accept_one_click"
                            name="p24_regulation_accept"
                            type="checkbox"
                            value="1"
                    >
                    <label class="przelewy24-checkbox__label" for="p24_regulation_accept_one_click">
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
                                    id="conditions_to_approve_one_click[{$condition_name}]"
                                    name="conditions_to_approve[{$condition_name}]"
                                    required
                                    type="checkbox"
                                    value="1"
                            >
                            <label class="przelewy24-checkbox__label js-terms" for="conditions_to_approve_one_click[{$condition_name}]">
                                {$condition|cleanHtml nofilter}
                            </label>
                        </div>
                    {/foreach}
                {/if}
            </div>

            {include file="module:przelewy24payment/views/templates/front/payment_additional_information.tpl" showIntro=false}

            <button
                    class="przelewy24-loader-button btn btn-primary js-przelewy24-saved-cards-submit mr-1"
                    type="submit"
                    disabled="disabled"
            >
                <div class="przelewy24-loader przelewy24-loader-button__loader">
                    <div class="przelewy24-loader__spinner"></div>
                </div>
                {l s='Pay with saved card' d='Modules.Przelewy24payment.Shop'}
            </button>
            <button class="przelewy24-saved-cards__add-card btn btn-sm btn-link js-przelewy24-add-card-button">
                {l s='+ Add new card' d='Modules.Przelewy24payment.Shop'}
            </button>
        </div>
    </div>
{/if}
