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
<div class="przelewy24-saved-cards js-przelewy24-saved-cards-wrapper">
    {if !empty($cards) && $cards->count() > 0}
        <div class="przelewy24-saved-cards__list">
            {foreach $cards as $card}
                <div class="przelewy24-saved-cards__item">
                    <div class="przelewy24-saved-cards__content">
                        <div class="przelewy24-saved-cards__label d-block text-xs-left {if !empty($card->getDefault())}przelewy24-saved-cards__label--active{/if}">
                            <div class="przelewy24-saved-cards__row">
                                {if !empty($card->getLogo())}
                                    <div class="przelewy24-saved-cards__col przelewy24-saved-cards__col--type text-xs-center">
                                        <img
                                                class="przelewy24-saved-cards__img img-fluid"
                                                loading="lazy"
                                                src="{$card->getLogo()}"
                                                alt="{$card->getType()}"
                                                title="{$card->getType()}"
                                        >
                                    </div>
                                {/if}
                                {if !empty($card->getMask())}
                                    <div class="przelewy24-saved-cards__col przelewy24-saved-cards__col--number">
                                        {if !empty($card->getType()) || !empty($card->getMask())}
                                            <div class="przelewy24-saved-cards__content">
                                                {if !empty($card->getMask())}
                                                    <span class="przelewy24-saved-cards__number">
                                                        {$card->getMask()}
                                                    </span>
                                                {/if}
                                            </div>
                                        {/if}
                                        {if !empty($card->getCardDate())}
                                            <span class="przelewy24-saved-cards__expiration">
                                                {l s='EXP' d='Modules.Przelewy24payment.Shop'}:
                                                {$card->getFormatedCardData()}
                                            </span>
                                        {/if}
                                    </div>
                                {/if}
                                {if !empty($card->getRefId())}
                                    <div class="przelewy24-saved-cards__col przelewy24-saved-cards__col--actions text-xs-right">
                                        {if !empty($card->getDefault())}
                                            <span class="przelewy24-saved-cards__default">
                                                {l s='Default card' d='Modules.Przelewy24payment.Shop'}
                                            </span>
                                        {else}
                                            <a
                                                    class="przelewy24-saved-cards__button btn btn-sm btn-link p-0 js-przelewy24-saved-cards-action-button"
                                                    href="{url entity='module' name='przelewy24payment' controller='cards' params=['setDefaultCard' => '1', 'refId' => $card->getRefId()]}"
                                            >
                                                {l s='Set as default' d='Modules.Przelewy24payment.Shop'}
                                            </a>
                                        {/if}
                                        <a
                                                class="przelewy24-saved-cards__button btn btn-sm btn-link text-danger p-0 js-przelewy24-saved-cards-action-button"
                                                href="{url entity='module' name='przelewy24payment' controller='cards' params=['removeCard' => '1', 'refId' => $card->getRefId()]}"
                                        >
                                            {l s='Remove' d='Modules.Przelewy24payment.Shop'}
                                        </a>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <div class="alert alert-info text-xs-center">
            {l s='No saved cards' d='Modules.Przelewy24payment.Shop'}
        </div>
    {/if}
    <div class="przelewy24-saved-cards__submit-wrapper text-xs-center mt-2 mb-1">
        <button class="przelewy24-saved-cards__add-card btn btn-sm btn-link js-przelewy24-add-card-button">
            {l s='+ Add new card' d='Modules.Przelewy24payment.Shop'}
        </button>
    </div>
</div>
