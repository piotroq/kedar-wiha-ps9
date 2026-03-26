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

{block 'przelewy24_alert_class'}js-przelewy24-order-confirmation-pending{/block}

{block 'przelewy24_alert_type'}info{/block}

{block 'przelewy24_alert_icon_wrapper'}
    <div class="przelewy24-loader przelewy24-order-confirmation__icon">
        <div class="przelewy24-loader__spinner"></div>
    </div>
{/block}

{block 'przelewy24_alert_message'}
    {l s='Thank you for your order, please wait for the payment confirmation.' d='Modules.Przelewy24payment.Shop'}
{/block}
