<?php

namespace DpdShipping\Support;

class GridActions
{
    public static function addOrderBulkActions($definition): void
    {
        if (!$definition || !method_exists($definition, 'getBulkActions')) {
            return;
        }

        $bulk = $definition->getBulkActions();
        if (!$bulk || !method_exists($bulk, 'add')) {
            return;
        }

        $translator = \Context::getContext()->getTranslator();

        $bulk->add(
            (new \PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction('dpdshipping_generate_shipping_bulk_action'))
                ->setName($translator->trans('DPD Poland - generate shipping', [], 'Modules.Dpdshipping.Bulk'))
                ->setOptions([
                    'submit_route' => 'dpdshipping_generate_shipping_bulk_action',
                    'submit_method' => 'POST',
                ])
        );

        $bulk->add(
            (new \PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction('dpdshipping_generate_labels_bulk_action'))
                ->setName($translator->trans('DPD Poland - generate labels', [], 'Modules.Dpdshipping.Bulk'))
                ->setOptions([
                    'submit_route' => 'dpdshipping_shipping_history_print_labels_form',
                    'submit_method' => 'POST',
                ])
        );

        $bulk->add(
            (new \PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction('dpdshipping_generate_shipping_and_labels_bulk_action'))
                ->setName($translator->trans('DPD Poland - generate shipping and labels', [], 'Modules.Dpdshipping.Bulk'))
                ->setOptions([
                    'submit_route' => 'dpdshipping_generate_shipping_and_labels_bulk_action',
                    'submit_method' => 'POST',
                ])
        );
    }
}
