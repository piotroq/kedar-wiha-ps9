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
{if !empty($paymentsMainList) && $paymentsMainList->count() > 0}
    <div class="przelewy24-payment-option-nested">
        <p class="przelewy24-payment-option-nested__title">
            {l s='Choose payment method' d='Modules.Przelewy24payment.Shop'}
        </p>
        <div class="przelewy24-payment-option-nested__list">
            {foreach $paymentsMainList as $payment}
                <div class="przelewy24-payment-option-nested__item">
                    <div class="przelewy24-payment-option-nested__content">
                        <input
                                class="przelewy24-payment-option-nested__input js-przelewy24-payment-option-nested-input"
                                type="radio"
                                {if !empty($payment->getId())}
                                    id="przelewy24-payment-option-nested-{$payment->getId()}"
                                {else}
                                    id="przelewy24-payment-option-nested-{$payment@index}"
                                {/if}
                                name="przelewy24-payment-option-nested"
                                {if !empty($payment->getFrontUrl())}
                                    value="{$payment->getFrontUrl()}"
                                {/if}
                        />
                        <label
                                class="przelewy24-payment-option-nested__label d-block"
                                {if !empty($payment->getId())}
                                    for="przelewy24-payment-option-nested-{$payment->getId()}"
                                {else}
                                    for="przelewy24-payment-option-nested-{$payment@index}"
                                {/if}
                        >
                            {if !empty($payment->getImgUrl())}
                                <div class="przelewy24-payment-option-nested__img-wrapper">
                                    <img
                                            class="przelewy24-payment-option-nested__img img-fluid"
                                            loading="lazy"
                                            src="{$payment->getImgUrl()}"
                                            {if !empty($payment->getSpecialName())}
                                                alt="{$payment->getSpecialName()}"
                                                title="{$payment->getSpecialName()}"
                                            {elseif !empty($payment->getName())}
                                                alt="{$payment->getName()}"
                                                title="{$payment->getName()}"
                                            {else}
                                                alt="{l s='Przelewy24 payment method image' d='Modules.Przelewy24payment.Shop'}"
                                                title="{l s='Przelewy24 payment method image' d='Modules.Przelewy24payment.Shop'}"
                                            {/if}
                                    >
                                </div>
                            {/if}
                        </label>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}

{include file="module:przelewy24payment/views/templates/front/payment_additional_information.tpl"}
