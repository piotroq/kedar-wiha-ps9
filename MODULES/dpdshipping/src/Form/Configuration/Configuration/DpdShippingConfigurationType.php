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

declare(strict_types=1);

namespace DpdShipping\Form\Configuration\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DpdShippingConfigurationType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logLevel', ChoiceType::class, [
                'choices' => [
                    $this->trans('ERROR', 'Modules.Dpdshipping.AdminConfiguration') => 'error',
                    $this->trans('INFO', 'Modules.Dpdshipping.AdminConfiguration') => 'info',
                    $this->trans('DEBUG', 'Modules.Dpdshipping.AdminConfiguration') => 'debug',
                ],
                'label' => $this->trans('Log level', 'Modules.Dpdshipping.AdminConfiguration'),
            ])
            ->add('customCheckout', ChoiceType::class, [
                'choices' => [
                    $this->trans('Standard Prestashop Checkout', 'Modules.Dpdshipping.AdminConfiguration') => 'standard',
                    $this->trans('Supercheckout', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_SUPERCHECKOUT,
                    $this->trans('Easycheckout', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_EASYCHECKOUT,
                    $this->trans('One Page Checkout - PrestaTeam - Prestashop 1.7', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_OPC_PRESTATEAM_1_7,
                    $this->trans('One Page Checkout - PrestaTeam - Prestashop 8', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_OPC_PRESTATEAM_8,
                    $this->trans('The Checkout - PrestaSmart', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_THECHECKOUT_PRESTASMART,
                    $this->trans('One Page Checkout - PrestaSmart', 'Modules.Dpdshipping.AdminConfiguration') => Configuration::CUSTOM_CHECKOUT_OPC_PRESTASMART,

                ],
                'label' => $this->trans('Custom checkout', 'Modules.Dpdshipping.AdminConfiguration'),
            ])
            ->add('sendMailWhenShippingGenerated', ChoiceType::class, [
                'choices' => [
                    $this->trans('Yes', 'Modules.Dpdshipping.AdminConfiguration') => '1',
                    $this->trans('No', 'Modules.Dpdshipping.AdminConfiguration') => '0',
                ],
                'label' => $this->trans('Send mail when shipping is generated', 'Modules.Dpdshipping.AdminConfiguration'),
            ])
            ->add('checkTrackingOrderView', ChoiceType::class, [
                'choices' => [
                    $this->trans('Yes', 'Modules.Dpdshipping.AdminConfiguration') => '1',
                    $this->trans('No', 'Modules.Dpdshipping.AdminConfiguration') => '0',
                ],
                'label' => $this->trans('Check shipment tracking on the order view page', 'Modules.Dpdshipping.AdminConfiguration'),
            ])
            ->add('empikModuleIntegrationEnabled', ChoiceType::class, [
                'choices' => [
                    $this->trans('Yes', 'Modules.Dpdshipping.AdminConfiguration') => '1',
                    $this->trans('No', 'Modules.Dpdshipping.AdminConfiguration') => '0',
                ],
                'label' => $this->trans('Integration with Empik marketplace', 'Modules.Dpdshipping.AdminConfiguration'),
            ])
            ->add('empikDpdApiForStoreDelivery', ChoiceType::class, [
                'choices' => [], //Override by DpdShippingConfigurationController
                'label' => $this->trans('API account for Empik Store delivery', 'Modules.Dpdshipping.AdminConfiguration'),
                'required' => false,
            ]);
    }
}
