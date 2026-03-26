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
use Przelewy24\Parser\Yaml\YamlParser;

class HooksInstaller implements InstallerInterface
{
    private $parsedYaml = [];

    public function install(ModuleConfiguration $configuration): bool
    {
        $yamlParser = new YamlParser();
        $this->parsedYaml = $yamlParser->parseYml(ModuleConfiguration::HOOKS_YML_FILE);
        $module = \Module::getInstanceByName(ModuleConfiguration::MODULE_NAME);

        return $module->registerHook($this->parsedYaml['hooks']);
    }
}
