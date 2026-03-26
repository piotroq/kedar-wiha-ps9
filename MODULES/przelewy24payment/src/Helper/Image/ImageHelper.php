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

namespace Przelewy24\Helper\Image;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;

class ImageHelper
{
    private $urlPath;

    private $path;

    private $context;

    private $module;

    public function __construct(\Context $context, \Module $module)
    {
        $this->context = $context;
        $this->module = $module;
        $this->urlPath = 'modules' . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . ModuleConfiguration::IMG_PATH . DIRECTORY_SEPARATOR;
        $this->path = _PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . ModuleConfiguration::IMG_PATH . DIRECTORY_SEPARATOR;
    }

    public function createUrl($name)
    {
        return $this->context->link->getAdminBaseLink() . $this->urlPath . $name . '?t=' . time();
    }

    public function fileExist($name)
    {
        return file_exists($this->path . $name);
    }

    public function getFilePath($name)
    {
        return $this->path . $name;
    }
}
