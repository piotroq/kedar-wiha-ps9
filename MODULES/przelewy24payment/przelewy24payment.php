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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Hook\HookExecutor;
use Przelewy24\Installer\ModuleInstaller;
use Przelewy24\Model\Przlewy24AccountModel;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

include_once _PS_MODULE_DIR_ . 'przelewy24payment/vendor/autoload.php';

class przelewy24payment extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'przelewy24payment';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.4';
        $this->author = 'Przelewy24';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('Przelewy24 Payments for PrestaShop', [], 'Modules.Przelewy24payment.Przelewy24payment');
        $this->description = $this->trans('Przelewy24 module for PrestaShop – fast, secure online payments supporting cards, BLIK, transfers, and wallets. Easy setup and increased sales for your store.', [], 'Modules.Przelewy24payment.Przelewy24payment');
        $this->controllers = ['default'];
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        $installer = new ModuleInstaller();
        $configuration = new ModuleConfiguration();

        $result = parent::install() && $installer->install($configuration) && Przlewy24AccountModel::fillAccount();

        if (80100 <= PHP_VERSION_ID) {
            return true;
        }

        try {
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->getService('event_dispatcher');
            $eventDispatcher->addListener(KernelEvents::TERMINATE, function () {
                Module::$_INSTANCE = [];
            }, -10000);
        } catch (Exception $e) {
        }

        return $result;
    }

    public function uninstall()
    {
        $installer = new ModuleInstaller();
        $configuration = new ModuleConfiguration();
        $result = $installer->uninstall($configuration) && parent::uninstall();

        if (80100 <= PHP_VERSION_ID) {
            return true;
        }

        try {
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->getService('event_dispatcher');
            $eventDispatcher->addListener(KernelEvents::TERMINATE, function () {
                Module::$_INSTANCE = [];
            }, -10000);
        } catch (Exception $e) {
        }

        return $result;
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function __call(string $methodName, array $params)
    {
        if ($this->context->controller instanceof Controller && $this->context->controller->getContainer() === null) {
            FrontController::$initialized = false;

            $this->context->controller->init();
        }

        $hookExecutor = $this->getService(HookExecutor::class);

        $methodName = ucfirst(substr($methodName, 4));

        return $hookExecutor->execute($methodName, $params);
    }

    public function getService($service)
    {
        if (
            $this->context->controller instanceof AdminController
            && Tools::version_compare(_PS_VERSION_, '1.7.8')
            && null !== $container = SymfonyContainer::getInstance()
        ) {
            return $container->get($service);
        }

        return parent::get($service);
    }

    private function isContainerConfigLoaded()
    {
        if ($this->active) {
            return true;
        }

        if (Tools::version_compare(_PS_VERSION_, '8.0.0') || Tools::version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            return true;
        }

        return $this->hasShopAssociations();
    }

    public function getContent()
    {
        try {
            $sfContainer = SymfonyContainer::getInstance();
            if (!$this->isContainerConfigLoaded()) {
                $this->addFlash($this->l('To access the configuration page, the module must be active.'));
                Tools::redirectAdmin($sfContainer->get('router')->generate('admin_module_manage'));
            }
            Context::getContext()->controller = null;
            Tools::redirectAdmin($sfContainer->get('router')->generate('przelewy24.index'));
        } catch (RouteNotFoundException $e) {
            if (!Tools::getValue('cache_cleared')) {
                Tools::clearSf2Cache();
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true, [], [
                    'configure' => $this->name,
                    'cache_cleared' => true,
                ]));
            }
            throw $e;
        }
    }

    private function addFlash(string $message, string $type = 'error'): void
    {
        try {
            $session = $this->get('session');
            $session->getFlashBag()->add($type, $message);
        } catch (ServiceNotFoundException $e) {
        }
    }

    public function hookPaymentOptions($params)
    {
        return $this->__call('hookPaymentOptions', [$params]);
    }
}
