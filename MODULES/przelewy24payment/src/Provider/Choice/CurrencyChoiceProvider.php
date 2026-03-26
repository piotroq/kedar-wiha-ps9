<?php
/**
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
 */

namespace Przelewy24\Provider\Choice;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Choice\Interfaces\ChoiceProviderInterface;

class CurrencyChoiceProvider implements ChoiceProviderInterface
{
    public function getChoices(): array
    {
        $accounts = Przlewy24AccountModel::getAllAccounts(\Context::getContext()->shop->id);

        $choices = [];
        foreach ($accounts as $account) {
            $choices[$account['iso_code']] = $account['id_account'];
        }

        return $choices;
    }
}
