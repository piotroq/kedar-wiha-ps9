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
    <section class="przelewy24-repayment">
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
                {l s='Przelewy24 repayment page' d='Modules.Przelewy24payment.Shop'}
            </h3>

            <p class="text-xs-center mb-0">
                {l s='Please choose the payment method you want to use to repay your order.' d='Modules.Przelewy24payment.Shop'}
            </p>
        </div>
        <div class="mb-2">
            <div class="row">
                <div class="col-xs-12 col-lg-7">
                    <div class="payment-options">
                        {foreach $payment_options as $option}
                            <div>
                                <div id="{$option.id}-container" class="przelewy24-repayment__payment-option payment-option clearfix">
                                    <span class="przelewy24-repayment__radio custom-radio float-xs-left">
                                        <input
                                                class="js-przelewy24-payment-input {if $option.binary} binary {/if}"
                                                id="{$option.id}"
                                                data-module-name="{$option.module_name}"
                                                name="payment-option"
                                                type="radio"
                                                required
                                        >
                                        <span></span>
                                    </span>

                                    <label class="przelewy24-repayment__label" for="{$option.id}">
                                        <span>{$option.call_to_action_text}</span>
                                        {if $option.logo}
                                            <img src="{$option.logo}">
                                        {/if}
                                    </label>
                                </div>
                            </div>
                            {if $option.additionalInformation}
                                <div
                                        id="{$option.id}-additional-information"
                                        class="js-przelewy24-additional-information js-additional-information przelewy24-repayment__additional-information definition-list hidden-xs-up"
                                        name="{$option.id}"
                                        style="display: none"
                                >
                                    {$option.additionalInformation nofilter}
                                </div>
                            {/if}
                            <div
                                    id="pay-with-{$option.id}-form"
                                    class="js-przelewy24-payment-option-form js-payment-option-form hidden-xs-up"
                                    name="{$option.id}"
                                    style="display: none"
                            >
                                {if $option.form}
                                    {$option.form|escape:'htmlall':'UTF-8' nofilter}
                                {else}
                                    <form id="payment-form" method="POST" action="{$option.action|escape:'htmlall':'UTF-8'}">
                                        {foreach from=$option.inputs item=input}
                                            <input type="{$input.type}" name="{$input.name}" value="{$input.value}">
                                        {/foreach}
                                        <button style="display:none" id="pay-with-{$option.id}" type="submit"></button>
                                    </form>
                                {/if}
                            </div>
                        {foreachelse}
                            <p class="alert alert-danger">{l s='Unfortunately, there are no payment method available.' d='Modules.Przelewy24payment.Shop'}</p>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        <div
                id="payment-confirmation"
                class="przelewy24-repayment__submit-wrapper"
        >
            <button
                    class="btn btn-primary js-przelewy24-repayment-submit"
                    type="submit"
                    disabled="disabled"
            >
                {l s='Repay order' d='Modules.Przelewy24payment.Shop'}
            </button>
        </div>
    </section>
{/block}
