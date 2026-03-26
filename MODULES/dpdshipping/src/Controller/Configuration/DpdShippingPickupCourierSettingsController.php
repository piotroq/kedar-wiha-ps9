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

namespace DpdShipping\Controller\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Domain\Configuration\PickupCourierSettings\Command\DeletePickupOrderSettingsAddressCommand;
use DpdShipping\Grid\Configuration\PickupCourierSettings\PickupCourierSettingsFilter;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Shop;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DpdShippingPickupCourierSettingsController extends FrameworkBundleAdminController
{
    private $commandBus;
    private $quoteGridFactory;
    private $textFormDataHandler;
    private $translator;

    public function __construct($commandBus, $quoteGridFactory, $textFormDataHandler, $translator)
    {
        $this->commandBus = $commandBus;
        $this->quoteGridFactory = $quoteGridFactory;
        $this->textFormDataHandler = $textFormDataHandler;
        $this->translator = $translator;
    }

    public function list(Request $request, PickupCourierSettingsFilter $filters): Response
    {
        $quoteGrid = $this->quoteGridFactory->getGrid($filters);

        return $this->render('@Modules/dpdshipping/views/templates/admin/configuration/pickup-order-settings-list-form.html.twig', [
            'enableSidebar' => true,
            'layoutTitle' => $this->translator->trans('Pickup order settings', [], 'Modules.Dpdshipping.Admin'),
            'quoteGrid' => $this->presentGrid($quoteGrid),
            'shopContext' => Shop::getContext()
        ]);
    }

    public function index(Request $request): Response
    {
        $textForm = $this->textFormDataHandler->getForm();
        $textForm->handleRequest($request);

        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $errors = $this->textFormDataHandler->save($textForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));

                return $this->redirectToRoute('dpdshipping_pickup_courier_settings_list_form');
            }

            $this->flashErrors($errors);
        }

        return $this->render('@Modules/dpdshipping/views/templates/admin/configuration/pickup-courier-settings-form.html.twig', [
            'form' => $textForm->createView(),
            'pickupCourierId' => $request->get('pickupCourierId'),
            'shopContext' => Shop::getContext()
        ]);
    }

    public function delete(Request $request): Response
    {
        $deleteSenderAddress = $this->commandBus->handle(new DeletePickupOrderSettingsAddressCommand($request->get('pickupCourierId')));
        if ($deleteSenderAddress) {
            $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
        }

        return $this->redirectToRoute('dpdshipping_pickup_courier_settings_list_form');
    }
}
