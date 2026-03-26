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
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Installer\Handler\HooksInstaller;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Przelewy24_1_1_1
{
    public function upgrade()
    {
        return $this->processDatabase();
    }

    public function processDatabase()
    {
        $result = true;

        $databaseInstaller = new HooksInstaller();
        $result &= $databaseInstaller->install(new ModuleConfiguration());

        return $result;
    }
}

/**
 * Upgrade module to version 1.1.0
 */
function upgrade_module_1_1_1(Module $module)
{
    $updater = new Przelewy24_1_1_1();

    return $updater->upgrade();
}
