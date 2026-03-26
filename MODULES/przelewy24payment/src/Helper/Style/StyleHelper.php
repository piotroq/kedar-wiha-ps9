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

namespace Przelewy24\Helper\Style;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StyleHelper
{
    public static function underscoreToCamelCase($string, $capitalizeFirstCharacter = true)
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    public static function seterForUnderscoreField($string, $capitalizeFirstCharacter = true)
    {
        return 'set' . self::underscoreToCamelCase($string, $capitalizeFirstCharacter);
    }

    public static function fillObject(object $object, array $data)
    {
        foreach ($data as $key => $value) {
            $seter = self::seterForUnderscoreField($key);
            if (is_callable([$object, $seter])) {
                $object->{$seter}($value);
            }
        }

        return $object;
    }
}
