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
{capture assign='przelewy24_alert_type_variable'}{block 'przelewy24_alert_type'}info{/block}{/capture}

<div
        class="{block 'przelewy24_alert_class'}{/block} {if !$visible|default:false}hidden-xs-up{/if}"
        {if !$visible|default:false}style="display: none"{/if}
>
    <div class="alert alert-{$przelewy24_alert_type_variable} text-xs-center mb-2">
        {block 'przelewy24_alert_icon_wrapper'}
            {block 'przelewy24_alert_icon' hide}
                <i class="przelewy24-order-confirmation__icon przelewy24-icon rtl-no-flip pt-0 mr-0">
                    {$Smarty.block.child}
                </i>
            {/block}
        {/block}
        {block 'przelewy24_alert_message' hide}
            <span class="przelewy24-order-confirmation__info d-block">
                {$Smarty.block.child}
            </span>
        {/block}
        {block 'przelewy24_alert_contact' hide}
            <span class="przelewy24-order-confirmation__info d-block">
                {$smarty.block.child}
            </span>
        {/block}
    </div>
    {block 'przelewy24_alert_extra_content' hide}
        {$smarty.block.child}
    {/block}
</div>
