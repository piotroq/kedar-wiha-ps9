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

namespace Przelewy24\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Installer\Handler\DatabaseInstaller;
use Przelewy24\Installer\Handler\HooksInstaller;
use Przelewy24\Installer\Handler\Interfaces\InstallerInterface;
use Przelewy24\Installer\Handler\Interfaces\UnInstallerInterface;
use Przelewy24\Installer\Handler\TranslationInstaller;

class ModuleInstaller implements InstallerInterface, UnInstallerInterface
{
    private $installers = [];
    private $uninstallers = [];

    public function __construct()
    {
        $this->prepareInstallers();
        $this->prepareUnInstallers();
    }

    private function prepareInstallers()
    {
        $this->installers[] = new DatabaseInstaller();
        $this->installers[] = new HooksInstaller();
        $this->installers[] = new TranslationInstaller();
    }

    private function prepareUnInstallers()
    {
        $this->uninstallers[] = new DatabaseInstaller();
    }

    public function install(ModuleConfiguration $configuration): bool
    {
        $result = true;
        foreach ($this->installers as $installer) {
            $result &= $installer->install($configuration);
        }

        return (bool) $result;
    }

    public function uninstall(ModuleConfiguration $configuration): bool
    {
        $result = true;
        foreach ($this->uninstallers as $uninstaller) {
            $result &= $uninstaller->uninstall($configuration);
        }

        return (bool) $result;
    }
}
