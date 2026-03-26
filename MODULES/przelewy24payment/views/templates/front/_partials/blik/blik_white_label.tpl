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

<div class="przelewy24-card-white-label__wrapper js-przelewy24-blik-white-label-wrapper hidden-xs-up" style="display: none">
    <div class="przelewy24-card-white-label__modal">
        <div class="przelewy24-card-white-label__loader-wrapper js-przelewy24-blik-white-label-loader">
            <div class="przelewy24-loader przelewy24-loader__icon przelewy24-loader__icon--lg przelewy24-loader__shaking">
                <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        style="width: 100%; height: auto; max-width: 64px;"
                >
                    <path d="M4 10v7h3v-7H4zm6 0v7h3v-7h-3zm6 0v7h3v-7h-3zM2 21h20v-2H2v2zm10-19L2 7v2h20V7l-10-5z"/>
                </svg>
            </div>
            <span class="przelewy24-loader__message przelewy24-loader__message--lg d-block text-xs-center font-weight-bold">
                {l s='Confirm the payment in your banking app' d='Modules.Przelewy24payment.Shop'}
            </span>
        </div>
        <div id="przelewy24-blik-white-label" class="przelewy24-card-white-label__iframe-wrapper"></div>
    </div>
</div>
