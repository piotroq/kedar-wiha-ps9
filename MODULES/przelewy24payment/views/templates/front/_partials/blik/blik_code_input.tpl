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

<div class="przelewy24-blik__code-wrapper form-group">
    <label class="przelewy24-blik__code-label form-control-label d-block" for="p24_blik_code">
        {l s='Enter the BLIK code' d='Modules.Przelewy24payment.Shop'}
    </label>
    <input
            class="przelewy24-blik__code-input js-przelewy24-blik-code-input form-control"
            name="p24_blik_code"
            id="p24_blik_code"
            type="text"
            placeholder=""
            minlength="6"
            maxlength="6"
            pattern="\d*"
            inputmode="numeric"
            value=""
            required
            autocomplete="off"
    >
</div>
