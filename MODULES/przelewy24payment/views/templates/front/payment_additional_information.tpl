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
{if !empty($intro)}
    <div class="przelewy24-intro mt-2 mb-1">
        {if $showIntro|default:true || (!empty($extra_charge_value) && $extra_charge_value > 0)}
            <img
                    class="img-fluid"
                    src="{$przelewy_logo}"
                    alt="{l s='Pay with Przelewy24' d='Modules.Przelewy24payment.Shop'}"
                    width="80"
                    height="28"
            >
        {/if}

        {if $showIntro|default:true}
            <p class="mt-1">
                {l s='After ordering you will be redirected to the service Przelewy24 to finish payments' d='Modules.Przelewy24payment.Shop'}
            </p>
        {/if}
    </div>

    {if (!empty($extra_charge_value) && $extra_charge_value > 0) && (!empty($extra_charge) || !empty($total))}
        <dl>
            {if !empty($extra_charge)}
                <dt>{l s='Extracharge Przelewy24' d='Modules.Przelewy24payment.Shop'}</dt>
                <dd>{$extra_charge}</dd>
            {/if}

            {if !empty($total)}
                <dt>{l s='Amount' d='Modules.Przelewy24payment.Shop'}</dt>
                <dd>{$total}</dd>
            {/if}
        </dl>
    {/if}
{/if}

{if !empty($selected) && $selected}
    <input
            class="js-przelewy24-payment-option-selected"
            type="hidden"
            value="{$selected}"
    >
{/if}

