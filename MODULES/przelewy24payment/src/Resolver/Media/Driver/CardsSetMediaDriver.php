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

class CardsSetMediaDriver implements ControllerSetMediaDriverInterface
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
        return [\Przelewy24paymentcardsModuleFrontController::class];
    }

    public function registerMedia(Config $config, Przelewy24Config $accountConfig)
    {
        if ($accountConfig->getCards()->getPaymentInStore()) {
            $config->getOptions()->cards = [
                'click_to_pay_guest' => (bool) $accountConfig->getCards()->getClickToPayGuest(),
                'click_to_pay' => (bool) $accountConfig->getCards()->getClickToPay(),
                'one_click_card' => (bool) ($accountConfig->getCards()->getOneClickCard() && $this->context->customer->isLogged()),
            ];

            if (
                ($accountConfig->getCards()->getClickToPay() && $this->context->customer->isLogged())
                || ($accountConfig->getCards()->getClickToPay() && $accountConfig->getCards()->getClickToPayGuest() && !$this->context->customer->isLogged())
            ) {
                $config->getOptions()->c2p = true;
                if (isset($this->context->customer->email) && !empty($this->context->customer->email)) {
                    $config->getOptions()->psu = ['email' => $this->context->customer->email];
                }
            } else {
                $config->getOptions()->c2p = false;
            }
        }

        $this->context->controller->registerStylesheet(
            $this->module->name . '-front',
            'modules/' . $this->module->name . '/views/css/front/' . ModuleConfiguration::PRZELEWY24 . '.css',
            [
                'media' => 'all',
            ]
        );
        $this->context->controller->registerJavascript(
            $this->module->name . '-cards',
            'modules/' . $this->module->name . '/views/js/front/' . ModuleConfiguration::PRZELEWY24 . '-cards.js',
            [
                'position' => 'bottom',
            ]
        );

        \Media::addJsDef(['_p24ConfigTokenization' => $config]);
    }

    public function modifyConfig(Config $config, Przelewy24Config $accountConfig)
    {
        if ($accountConfig->getCards()->getPaymentInStore()) {
            $config->getOptions()->cards = [
                'click_to_pay_guest' => (bool) $accountConfig->getCards()->getClickToPayGuest(),
                'click_to_pay' => (bool) $accountConfig->getCards()->getClickToPay(),
                'one_click_card' => (bool) ($accountConfig->getCards()->getOneClickCard() && $this->context->customer->isLogged()),
            ];

            if (
                ($accountConfig->getCards()->getClickToPay() && $this->context->customer->isLogged())
                || ($accountConfig->getCards()->getClickToPay() && $accountConfig->getCards()->getClickToPayGuest() && !$this->context->customer->isLogged())
            ) {
                $config->getOptions()->c2p = true;
                if (isset($this->context->customer->email) && !empty($this->context->customer->email)) {
                    $config->getOptions()->psu = ['email' => $this->context->customer->email];
                }
            } else {
                $config->getOptions()->c2p = false;
            }
        }

        $config->getOptions()->size = ['height' => '330px'];
        $config->getOptions()->agreement = [
            'contentEnabled' => [
                'enabled' => false,
                'checkboxEnabled' => false,
            ],
        ];
    }
}
