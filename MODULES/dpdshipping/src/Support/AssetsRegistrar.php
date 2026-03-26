<?php

namespace DpdShipping\Support;

use DpdShipping\Domain\Configuration\Carrier\DpdCarrierPrestashopConfiguration;
use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration as ConfigurationAlias;

class AssetsRegistrar
{
    private static function addJs($module, $context, string $handle, string $relativePath, int $priority = 150, bool $withServer = true): void
    {
        if (!$context || !$context->controller) {
            return;
        }
        $options = ['position' => 'bottom', 'priority' => $priority];
        if ($withServer) {
            $options['server'] = 'remote';
        }
        $context->controller->registerJavascript($handle, $module->getPathUri() . $relativePath, $options);
    }

    private static function addCss($module, $context, string $handle, string $relativePath, int $priority = 50, string $media = 'all', bool $withServer = true): void
    {
        if (!$context || !$context->controller) {
            return;
        }
        $options = ['media' => $media, 'priority' => $priority];
        if ($withServer) {
            $options['server'] = 'remote';
        }
        $context->controller->registerStylesheet($handle, $module->getPathUri() . $relativePath, $options);
    }

    public static function register($module, $context): void
    {
        if (!$context || !isset($context->controller) || !$context->controller) {
            return;
        }

        self::addJs($module, $context, 'dpdshipping-common-js', 'views/js/dpdshipping-common.js', 100, false);
        self::addCss($module, $context, 'dpdshipping-common-css', 'views/css/dpdshipping-common.css');

        $custom_cart = DpdCarrierPrestashopConfiguration::getConfig(ConfigurationAlias::CUSTOM_CHECKOUT);
        $custom_cart_array = [
            ConfigurationAlias::CUSTOM_CHECKOUT_SUPERCHECKOUT,
            ConfigurationAlias::CUSTOM_CHECKOUT_EASYCHECKOUT,
            ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTASMART,
            ConfigurationAlias::CUSTOM_CHECKOUT_THECHECKOUT_PRESTASMART,
            ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTATEAM_8,
            ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTATEAM_1_7,
        ];

        if (in_array($custom_cart, $custom_cart_array, true)) {
            self::addCss($module, $context, 'dpdshipping-pudo-customcheckout-css', 'views/css/dpdshipping-pudo-customcheckout.css');

            switch ($custom_cart) {
                case ConfigurationAlias::CUSTOM_CHECKOUT_SUPERCHECKOUT:
                    self::addJs($module, $context, 'dpdshipping-pudo-supercheckout-js', 'views/js/dpdshipping-pudo-supercheckout.js');
                    break;
                case ConfigurationAlias::CUSTOM_CHECKOUT_EASYCHECKOUT:
                    self::addJs($module, $context, 'dpdshipping-pudo-easycheckout-js', 'views/js/dpdshipping-pudo-easycheckout.js');
                    break;
                case ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTASMART:
                    self::addJs($module, $context, 'dpdshipping-pudo-opcprestasmart-js', 'views/js/dpdshipping-pudo-opc-prestasmart.js');
                    break;
                case ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTATEAM_8:
                    self::addJs($module, $context, 'dpdshipping-pudo-opcprestateam8-js', 'views/js/dpdshipping-pudo-opc-prestateam-8.js');
                    break;
                case ConfigurationAlias::CUSTOM_CHECKOUT_OPC_PRESTATEAM_1_7:
                    self::addJs($module, $context, 'dpdshipping-pudo-opcprestateam17-js', 'views/js/dpdshipping-pudo-opc-prestateam-1.7.js');
                    break;
                case ConfigurationAlias::CUSTOM_CHECKOUT_THECHECKOUT_PRESTASMART:
                    self::addJs($module, $context, 'dpdshipping-pudo-thecheckout-prestasmart-js', 'views/js/dpdshipping-pudo-thecheckout-prestasmart.js');
                    break;
            }
        } else {
            self::addJs($module, $context, 'dpdshipping-pudo-default-js', 'views/js/dpdshipping-pudo-default.js');
            self::addCss($module, $context, 'dpdshipping-pudo-default-css', 'views/css/dpdshipping-pudo-default.css');
        }

        self::addJs($module, $context, 'dpdshipping_pudo_iframe_js', 'views/js/dpdshipping-pudo-iframe.js');
        self::addJs($module, $context, 'dpdshipping_pudo_cod_iframe_js', 'views/js/dpdshipping-pudo-cod-iframe.js');
        self::addJs($module, $context, 'dpdshipping_pudo_swipbox_iframe_js', 'views/js/dpdshipping-pudo-swipbox-iframe.js');
    }
}
