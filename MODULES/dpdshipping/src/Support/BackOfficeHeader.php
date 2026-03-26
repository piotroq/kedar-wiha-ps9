<?php

namespace DpdShipping\Support;

use DpdShipping\Support\RouterHelper;

class BackOfficeHeader
{
    public static function register($module, $context, $params = []): void
    {
        if (!$context || !$context->controller || !$context->link) {
            return;
        }

        $translator = \Context::getContext()->getTranslator();

        $controller = \Tools::getValue('controller');
        $context->controller->addJS($module->getPathUri() . 'views/js/dpdshipping-ajax.js?' . time());

        if ($controller === 'AdminOrders') {
            $ajaxUrl = $context->link->getAdminLink('AdminModules', true, [], [
                'route' => 'dpdshipping_generate_shipping_bulk_action',
            ]);

            \Media::addJsDef([
                'dpdshipping_bulk_generate_shipping' => $ajaxUrl,
                'dpdshipping_translations' => [
                    'dpdshipping_return_label_success_text' => $translator->trans('The return label has been generated.', [], 'Modules.Dpdshipping.ReturnLabel'),
                    'dpdshipping_return_label_error_text' => $translator->trans('The return label cannot be generated.', [], 'Modules.Dpdshipping.ReturnLabel'),
                    'dpdshipping_label_success_text' => $translator->trans('The label has been generated.', [], 'Modules.Dpdshipping.ReturnLabel'),
                    'dpdshipping_label_error_text' => $translator->trans('The label cannot be generated.', [], 'Modules.Dpdshipping.ReturnLabel'),
                ],
            ]);
        } else {
            \Media::addJsDef([
                'dpdshipping_pickup_courier_ajax_url' => RouterHelper::generateRouteUrl($module, 'dpdshipping_pickup_courier_get_pickup_courier_settings_ajax'),
                'dpdshipping_pickup_courier_get_pickup_time_frames_ajax_url' => RouterHelper::generateRouteUrl($module, 'dpdshipping_pickup_courier_get_pickup_courier_time_frames_ajax'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_url' => RouterHelper::generateRouteUrl($module, 'dpdshipping_pickup_courier_pickup_courier_ajax'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_customer' => $translator->trans('Please complete the customer details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_sender' => $translator->trans('Please complete the sender details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_payer' => $translator->trans('Please complete the payer details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_pickup_date_time' => $translator->trans('Please complete pickup date and time range details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_parcel' => $translator->trans('Please complete parcels details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_letters' => $translator->trans('Please complete letters details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_packages' => $translator->trans('Please complete all packages details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_ajax_empty_palette' => $translator->trans('Please complete all palette details.', [], 'Modules.Dpdshipping.PickupCourier'),
                'dpdshipping_pickup_courier_pickup_courier_empty_configuration' => $translator->trans('Please complete the configuration in the module settings before ordering a courier', [], 'Modules.Dpdshipping.PickupCourier')
            ]);
        }

        \Media::addJsDef([
            'dpdshipping_token' => sha1(_COOKIE_KEY_ . 'dpdshipping')
        ]);
    }
}
