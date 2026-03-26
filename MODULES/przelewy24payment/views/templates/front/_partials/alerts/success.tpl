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

{block 'przelewy24_alert_class'}js-przelewy24-order-confirmation-success{/block}

{block 'przelewy24_alert_type'}success{/block}

{block 'przelewy24_alert_icon'}
    <svg xmlns="http://www.w3.org/2000/svg" height="30px" viewBox="0 -960 960 960" width="30px" fill="currentColor">
        <path d="m424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
    </svg>
{/block}

{block 'przelewy24_alert_message'}
    {l s='Your payment has been accepted.' d='Modules.Przelewy24payment.Shop'}
{/block}

{block 'przelewy24_alert_extra_content'}
    <div class="my-3 text-xs-center">
        <h3 class="przelewy24-order-confirmation__title h2">
            {l s='Your order is confirmed!' d='Modules.Przelewy24payment.Shop'}
        </h3>

        <p class="przelewy24-order-confirmation__text">
            {l
                s='An email has been sent to your mail address %email%.'
                d='Modules.Przelewy24payment.Shop'
                sprintf=['%email%' => $customer.email]
            }
        </p>
    </div>

    <div class="my-3 text-xs-center">
        <h3 class="h5">
            {l s='Order details' d='Modules.Przelewy24payment.Shop'}:
        </h3>
        <ul class="przelewy24-order-confirmation__list">
            <li class="przelewy24-order-confirmation__list-item">
                {l
                    s='Order reference: %reference%'
                    d='Modules.Przelewy24payment.Shop'
                    sprintf=['%reference%' => $presented_order.details.reference]
                }
            </li>
            <li class="przelewy24-order-confirmation__list-item">
                {l
                    s='Payment method: %method%'
                    d='Modules.Przelewy24payment.Shop'
                    sprintf=['%method%' => $presented_order.details.payment]
                }
            </li>
            {if !$presented_order.details.is_virtual}
                <li class="przelewy24-order-confirmation__list-item">
                    {l
                        s='Shipping method: %method%'
                        d='Modules.Przelewy24payment.Shop'
                        sprintf=['%method%' => $presented_order.carrier.name]
                    }
                    <br>
                    <em>{$presented_order.carrier.delay}</em>
                </li>
            {/if}
        </ul>
    </div>
{/block}
