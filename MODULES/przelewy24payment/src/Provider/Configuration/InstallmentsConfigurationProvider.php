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

namespace Przelewy24\Provider\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Dto\InstallmentsConfig;
use Przelewy24\Provider\Configuration\Interfaces\ConfigurationProviderInterface;

class InstallmentsConfigurationProvider extends AbstractConfigurationProvider implements ConfigurationProviderInterface
{
    protected function getType(): string
    {
        return 'installments';
    }

    protected function getObject(): object
    {
        return new InstallmentsConfig();
    }
}
