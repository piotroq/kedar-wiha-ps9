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

namespace Przelewy24\Provider\Status;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Dto\StatusNotify;
use Przelewy24\Helper\Style\StyleHelper;

class StatusNotifyProvider
{
    public function getStatusNotify(array $data)
    {
        $statusNotify = new StatusNotify();
        foreach ($data as $key => $value) {
            $seter = StyleHelper::seterForUnderscoreField($key);
            if (is_callable([$statusNotify, $seter])) {
                $statusNotify->{$seter}($value);
            }
        }

        return $statusNotify;
    }
}
