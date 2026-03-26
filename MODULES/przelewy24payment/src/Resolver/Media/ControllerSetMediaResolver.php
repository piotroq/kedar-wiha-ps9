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

namespace Przelewy24\Resolver\Media;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Factory\Javascript\ConfigurationFactory;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Resolver\Media\Driver\Interfaces\ControllerSetMediaDriverInterface;

class ControllerSetMediaResolver
{
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    private $config;

    private $resolvers;

    /**
     * @var \Context
     */
    private $context;

    /**
     * @var Przelewy24Config
     */
    private $accountConfig;

    public function __construct(
        ConfigurationFactory $configurationFactory,
        \Context $context,
        Przelewy24Config $accountConfig,
        $resolvers
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->config = $this->configurationFactory->factory();
        $this->resolvers = $resolvers;
        $this->context = $context;
        $this->accountConfig = $accountConfig;
    }

    public function resolve($controllerInstance)
    {
        try {
            $account = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($this->context->currency->id, $this->context->shop->id);
            $this->accountConfig->setAccount($account, false, true);
        } catch (\Exception $e) {
            return;
        }
        $resolver = $this->_getResolver($controllerInstance);
        if (!$resolver) {
            return;
        }
        $resolver->modifyConfig($this->config, $this->accountConfig);

        return $resolver->registerMedia($this->config, $this->accountConfig);
    }

    private function _getResolver($controllerInstance)
    {
        /* @var ControllerSetMediaDriverInterface $resolver */
        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->getControllers() as $controller) {
                if ($controllerInstance instanceof $controller) {
                    return $resolver;
                }
            }
        }

        return null;
    }
}
