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

namespace Przelewy24\Resolver\Media\Driver;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Dto\Javascript\Config;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Resolver\Media\Driver\Interfaces\ControllerSetMediaDriverInterface;

class ConfirmationSetMediaDriver implements ControllerSetMediaDriverInterface
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \Module
     */
    private $module;

    public function __construct(\Context $context, \Module $module)
    {
        $this->context = $context;
        $this->module = $module;
    }

    public function getControllers(): array
    {
        return [\Przelewy24paymentconfirmationModuleFrontController::class];
    }

    public function registerMedia(Config $config, Przelewy24Config $accountConfig)
    {
        $this->context->controller->registerStylesheet(
            $this->module->name . '-front',
            'modules/' . $this->module->name . '/views/css/front/' . ModuleConfiguration::PRZELEWY24 . '.css',
            [
                'media' => 'all',
            ]
        );
        $this->context->controller->registerJavascript(
            $this->module->name . '-confirmation',
            'modules/' . $this->module->name . '/views/js/front/' . ModuleConfiguration::PRZELEWY24 . '-confirmation.js',
            [
                'position' => 'bottom',
            ]
        );
    }

    public function modifyConfig(Config $config, Przelewy24Config $accountConfig)
    {
        // TODO: Implement modifyConfig() method.
    }
}
