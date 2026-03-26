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
<form class="przelewy24-repayment__additional-information" id="payment-form" method="POST" action="{$form_action}">
    <div class="form-group przelewy24-checkbox-group">
        <input
                class="przelewy24-checkbox__input"
                id="p24_regulation_accept_{$payment_method_id}"
                name="p24_regulation_accept"
                type="checkbox"
                value="1"
        >
        <label class="przelewy24-checkbox__label" for="p24_regulation_accept_{$payment_method_id}">
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
</form>
