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
{extends 'module:przelewy24payment/views/templates/front/_partials/base_alert.tpl'}

{block 'przelewy24_alert_class'}js-przelewy24-order-confirmation-danger{/block}

{block 'przelewy24_alert_type'}danger{/block}

{block 'przelewy24_alert_icon'}
    <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="currentColor">
        <path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
    </svg>
{/block}

{block 'przelewy24_alert_message'}
    {l s='There was a problem with your payment.' d='Modules.Przelewy24payment.Shop'}
{/block}

{block 'przelewy24_alert_contact'}
    {l s='Please contact the store owner:' d='Modules.Przelewy24payment.Shop'}
    {mailto address=$shop.email}
{/block}

{block 'przelewy24_alert_extra_content'}
    {if !empty($repayment_url)}
        <div class="text-xs-center mb-1">
            <a
                    class="btn btn-primary"
                    href="{$repayment_url}"
                    title="{l s='Repay your order' d='Modules.Przelewy24payment.Shop'}"
            >
                {l s='Repay your order' d='Modules.Przelewy24payment.Shop'}
            </a>
        </div>
    {/if}
{/block}
