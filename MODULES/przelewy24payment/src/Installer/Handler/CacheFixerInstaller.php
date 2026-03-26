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

namespace Przelewy24\Installer\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Installer\Handler\Interfaces\InstallerInterface;

class CacheFixerInstaller implements InstallerInterface
{
    public function install(ModuleConfiguration $configuration): bool
    {
        $module = \Module::getInstanceByName(ModuleConfiguration::MODULE_NAME);
        try {
            if (80100 > PHP_VERSION_ID) {
                /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
                $eventDispatcher = $module->get('event_dispatcher');
                $eventDispatcher->addListener(\Symfony\Component\HttpKernel\KernelEvents::TERMINATE, function () {
                    if (method_exists(\Module::class, 'resetStaticCache')) {
                        \Module::resetStaticCache();
                    }
                }, -10000);
            }
        } catch (\Exception $e) {
        }

        return true;
    }
}
