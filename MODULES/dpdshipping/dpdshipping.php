<?php
/**
 * Copyright 2024 DPD Polska Sp. z o.o.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    DPD Polska Sp. z o.o.
 * @copyright 2024 DPD Polska Sp. z o.o.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use DpdShipping\Config\Config;
use DpdShipping\Domain\Configuration\Carrier\Command\UpdateCarrierActionCommand;
use DpdShipping\Domain\Configuration\Carrier\DpdCarrierPrestashopConfiguration;
use DpdShipping\Domain\Configuration\Carrier\DpdIframe;
use DpdShipping\Domain\Configuration\Configuration\Query\GetConfiguration;
use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration as ConfigurationAlias;
use DpdShipping\Domain\Legacy\SpecialPrice\SpecialPriceService;
use DpdShipping\Hook\Hook;
use DpdShipping\Hook\HookRepository;
use DpdShipping\Install\AdminMenuTab;
use DpdShipping\Install\InstallerFactory;
use DpdShipping\Support\AssetsRegistrar;
use DpdShipping\Support\BackOfficeHeader;
use DpdShipping\Support\ContainerHelper;
use DpdShipping\Support\GridActions;
use DpdShipping\Support\RouterHelper;

class DpdShipping extends CarrierModule
{
    public $id_carrier;

    public function __construct()
    {
        $this->name = 'dpdshipping';
        $this->version = '2.0.1';
        $this->author = 'DPD Poland sp. z o. o.';
        $this->need_instance = 1;

        parent::__construct();

        $this->displayName = $this->trans('DPD Poland sp. z o. o. shipping module', [], 'Modules.Dpdshipping.Admin');
        $this->description = $this->trans('DPD Poland sp. z o. o. shipping module', [], 'Modules.Dpdshipping.Admin');

        $this->ps_versions_compliancy = [
            'min' => '1.7.8.0',
            'max' => '9.99.99',
        ];
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (!$this->context || !$this->context->link) {
            return;
        }

        $idAddressDelivery = isset($this->context->cart) ? (int) $this->context->cart->id_address_delivery : 0;

        Media::addJsDef([
            'dpdshipping_pickup_save_point_ajax_url' => $this->context->link->getModuleLink('dpdshipping', 'PickupSavePointAjax'),
            'dpdshipping_pickup_get_address_ajax_url' => $this->context->link->getModuleLink('dpdshipping', 'PickupGetAddressAjax'),
            'dpdshipping_pickup_is_point_with_cod_ajax_url' => $this->context->link->getModuleLink('dpdshipping', 'PickupIsCodPointAjax'),
            'dpdshipping_token' => sha1(_COOKIE_KEY_ . 'dpdshipping'),
            'dpdshipping_csrf' => Tools::getToken(false),
            'dpdshipping_id_cart' => isset($this->context->cart) ? (int) $this->context->cart->id : 0,
            'dpdshipping_iframe_url' => DpdIframe::getPickupIframeUrl(Config::DPD_PICKUP_MAP_URL_WITH_FILTERS, Config::PICKUP_MAP_BASE_URL, $idAddressDelivery),
            'dpdshipping_iframe_cod_url' => DpdIframe::getPickupIframeUrl(Config::DPD_PICKUP_COD_MAP_URL_WITH_FILTERS, Config::PICKUP_MAP_BASE_URL . '&direct_delivery_cod=1', $idAddressDelivery),
            'dpdshipping_iframe_swipbox_url' => DpdIframe::getPickupIframeUrl(Config::DPD_SWIP_BOX_MAP_URL_WITH_FILTERS, Config::PICKUP_MAP_BASE_URL . '&swip_box=1&hideFilters=1', $idAddressDelivery),
            'dpdshipping_id_pudo_carrier' => DpdCarrierPrestashopConfiguration::getConfig(Config::DPD_PICKUP),
            'dpdshipping_id_pudo_cod_carrier' => DpdCarrierPrestashopConfiguration::getConfig(Config::DPD_PICKUP_COD),
            'dpdshipping_id_pudo_swipbox_carrier' => DpdCarrierPrestashopConfiguration::getConfig(Config::DPD_SWIP_BOX),
        ]);

        $this->registerPudoFrontendAssets();
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        $this->registerPudoFrontendAssets();

        if (!$this->context || !$this->context->controller) {
            return false;
        }

        if (!empty($params) && isset($params['carrier']['id']) && DpdCarrierPrestashopConfiguration::isPickup($params['carrier']['id'])) {
            return $this->display(__FILE__, 'views/templates/hook/carrier-extra-content-pudo.tpl');
        }

        if (!empty($params) && isset($params['carrier']['id']) && DpdCarrierPrestashopConfiguration::isPickupCod($params['carrier']['id'])) {
            return $this->display(__FILE__, 'views/templates/hook/carrier-extra-content-pudo-cod.tpl');
        }

        if (!empty($params) && isset($params['carrier']['id']) && DpdCarrierPrestashopConfiguration::isPickupSwipBox($params['carrier']['id'])) {
            return $this->display(__FILE__, 'views/templates/hook/carrier-extra-content-pudo-swipbox.tpl');
        }

        return false;
    }

    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }
        $connection = ContainerHelper::getDatabaseConnectionForInstaller($this);
        if (!$connection) {
            return false;
        }

        $installer = InstallerFactory::create(new HookRepository(), $connection);

        if (!$installer->install($this)) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        $connection = ContainerHelper::getDatabaseConnectionForInstaller($this);
        if (!$connection) {
            return parent::uninstall();
        }
        $installer = InstallerFactory::create(new HookRepository(), $connection);

        return $installer->uninstall() && parent::uninstall();
    }

    public function getTabs(): array
    {
        $name = $this->trans('DPD Poland shipping', [], 'Modules.Dpdshipping.Admin');
        return AdminMenuTab::getTabs($name);
    }

    public function getContent()
    {
        $queryBus = ContainerHelper::getQueryBus($this);
        $needOnboarding = $queryBus ? $queryBus->handle(new GetConfiguration(ConfigurationAlias::NEED_ONBOARDING)) : null;

        if ($needOnboarding == null || $needOnboarding->getValue() == '1') {
            Tools::redirectAdmin(RouterHelper::generateRouteUrl($this, 'dpdshipping_onboarding_form'));
        } else {
            Tools::redirectAdmin(RouterHelper::generateRouteUrl($this, 'dpdshipping_connection_form'));
        }
    }

    public function hookDisplayAdminOrderTabLink(array $params)
    {
        return $this->get('prestashop.module.dpdshipping.hook.factory')->renderView(Hook::$DISPLAY_ADMIN_ORDER_TAB_LINK, $params, $this->get('twig'));
    }

    public function hookDisplayAdminOrderTabContent(array $params)
    {
        $controller = $this->get('prestashop.module.dpdshipping.controller.dpdshipping_tracking');
        return $this->get('prestashop.module.dpdshipping.hook.factory')->renderView(Hook::$DISPLAY_ADMIN_ORDER_TAB_CONTENT, $params, $controller);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        BackOfficeHeader::register($this, $this->context, $params);
    }

    public function hookActionOrderGridDefinitionModifier($params)
    {
        if (isset($params['definition'])) {
            GridActions::addOrderBulkActions($params['definition']);
        }
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    private function registerPudoFrontendAssets(): void
    {
        if (!$this->context || !$this->context->controller) {
            return;
        }
        AssetsRegistrar::register($this, $this->context);
    }


    public function getOrderShippingCost($params, $shipping_cost)
    {
        if (Configuration::get(ConfigurationAlias::SPECIAL_PRICE_ENABLED) == '1') {
            return $this->getOrderShippingCostExternal($params);
        }

        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        $specialPrice = new SpecialPriceService($params, $this->id_carrier);

        return $specialPrice->handle();
    }

    public function hookDisplayAdminOrderMain(array $params)
    {
        $factory = ContainerHelper::getFromContainer($this, 'prestashop.module.dpdshipping.hook.factory');
        return $factory ? $factory->renderView(Hook::$DISPLAY_ADMIN_ORDER_MAIN, $params, $this->get('prestashop.module.dpdshipping.controller.dpdshipping_generate_shipping')) : '';
    }

    public function hookActionCarrierUpdate(array $params)
    {
        $commandBus = ContainerHelper::getCommandBus($this);
        if ($commandBus) {
            $commandBus->handle(new UpdateCarrierActionCommand($params));
        }
    }
}
