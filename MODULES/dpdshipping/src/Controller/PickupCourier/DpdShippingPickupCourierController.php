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

namespace DpdShipping\Controller\PickupCourier;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use DateTime;
use DpdShipping\Api\DpdServices\Type\SenderPlaceV1;
use DpdShipping\Domain\Configuration\PickupCourier\Command\AddPickupCourierCommand;
use DpdShipping\Domain\Configuration\PickupCourier\Command\CancelPickupCourierCommand;
use DpdShipping\Domain\Configuration\PickupCourier\Query\GetCourierOrderAvailability;
use DpdShipping\Domain\Configuration\PickupCourierSettings\Query\GetPickupCourierSettingsList;
use DpdShipping\Domain\Order\Query\GetCountryIsoCode;
use DpdShipping\Grid\PickupCourier\Definition\Factory\PickupCourierGridDefinitionFactory;
use DpdShipping\Grid\PickupCourier\PickupCourierFilters;
use DpdShipping\Util\ValidateUserUtil;
use PrestaShop\Bundle\Grid\ResponseBuilder;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use ReflectionClass;
use Shop;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DpdShippingPickupCourierController extends FrameworkBundleAdminController
{
    private $queryBus;
    private $commandBus;
    private $quoteGridFactory;
    private $translator;
    private $responseBuilder;
    private $pickupCourierGridDefinitionFactory;
    private $pickupCourierEditFormDataHandler;

    public function __construct($queryBus, $commandBus, $quoteGridFactory, $translator,  $responseBuilder,  $pickupCourierGridDefinitionFactory, $pickupCourierEditFormDataHandler)
    {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->quoteGridFactory = $quoteGridFactory;
        $this->translator = $translator;
        $this->responseBuilder = $responseBuilder;
        $this->pickupCourierGridDefinitionFactory = $pickupCourierGridDefinitionFactory;
        $this->pickupCourierEditFormDataHandler = $pickupCourierEditFormDataHandler;
    }

    public function index(PickupCourierFilters $filters)
    {
        $quoteGrid = $this->quoteGridFactory->getGrid($filters);
        $gridData = $quoteGrid->getData();
        $records = $gridData->getRecords();
        $modifiedRecords = [];
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        foreach ($records as $record) {
            $pickupDate = DateTime::createFromFormat('d-m-Y', $record['pickup_date']);
            if ($record['state'] !== 'CANCELLED' && $today > $pickupDate)
                $record['state'] = $this->translator->trans('Completed', [], 'Modules.Dpdshipping.PickupCourierGrid');
            else if ($record['state'] === 'CANCELLED')
                $record['state'] = $this->translator->trans('Canceled', [], 'Modules.Dpdshipping.PickupCourierGrid');
            else if ($record['state'] === 'OK')
                $record['state'] = $this->translator->trans('Ordered', [], 'Modules.Dpdshipping.PickupCourierGrid');

            $modifiedRecords[] = $record;
        }

        $newRecordCollection = new RecordCollection($modifiedRecords);

        $newGridData = new GridData($newRecordCollection, $gridData->getRecordsTotal(), $gridData->getQuery());

        $quoteGridReflection = new ReflectionClass($quoteGrid);
        $dataProperty = $quoteGridReflection->getProperty('data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($quoteGrid, $newGridData);

        return $this->render(
            '@Modules/dpdshipping/views/templates/admin/pickupCourier/pickup-courier-form.html.twig',
            [
                'enableSidebar' => true,
                'layoutTitle' => $this->translator->trans('Shipping history', [], 'Modules.Dpdshipping.Admin'),
                'quoteGrid' => $this->presentGrid($quoteGrid),
                'shopContext' => Shop::getContext()
            ]
        );
    }

    public function edit(Request $request)
    {
        $textFormDataHandler = $this->pickupCourierEditFormDataHandler;
        $textForm = $textFormDataHandler->getForm();
        $textForm->handleRequest($request);
        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $errors = $textFormDataHandler->save($textForm->getData());
            if (empty($errors)) {
                $this->addFlash('success', $this->translator->trans('Successful update.', [], 'Admin.Notifications.Success'));
                return $this->redirectToRoute('dpdshipping_pickup_courier_form');
            }
            $this->flashErrors($errors);
        }
        return $this->render('@Modules/dpdshipping/views/templates/admin/pickupCourier/pickup-courier-edit-form.html.twig', [
            'form' => $textForm->createView(),
            'shopContext' => Shop::getContext()
        ]);
    }

    public function searchAction(Request $request)
    {
        return $this->responseBuilder->buildSearchResponse(
            $this->pickupCourierGridDefinitionFactory,
            $request,
            PickupCourierGridDefinitionFactory::GRID_ID,
            'dpdshipping_pickup_courier_form'
        );
    }

    public function getPickupCourierSettingsAjax(Request $request): JsonResponse
    {
        if (!ValidateUserUtil::validateEmployeeSession($request))
            return new JsonResponse(['success' => false, 'data' => 'INVALID TOKEN']);

        $pickupCourierId = $request->get('pickupOrderSettingsId');
        if ($pickupCourierId == null)
            return new JsonResponse(['success' => false]);

        $pickupCourier = $this->queryBus->handle(new GetPickupCourierSettingsList(false, $pickupCourierId));

        return new JsonResponse(['success' => true, 'data' => [
            'pickupCourierId' => $pickupCourierId,
            'customerFullName' => $pickupCourier->getCustomerFullName(),
            'customerName' => $pickupCourier->getCustomerName(),
            'customerPhone' => $pickupCourier->getCustomerPhone(),
            'payerNumber' => $pickupCourier->getPayerNumber(),
            'senderAddress' => $pickupCourier->getSenderAddress(),
            'senderCity' => $pickupCourier->getSenderCity(),
            'senderFullName' => $pickupCourier->getSenderFullName(),
            'senderName' => $pickupCourier->getSenderName(),
            'senderPhone' => $pickupCourier->getSenderPhone(),
            'senderPostalCode' => $pickupCourier->getSenderPostalCode(),
            'senderCountryCode' => $pickupCourier->getSenderCountryCode(),
        ]]);
    }

    public function pickupCourierAjax(Request $request): JsonResponse
    {
        if (!ValidateUserUtil::validateEmployeeSession($request))
            return new JsonResponse(['success' => false, 'data' => 'INVALID TOKEN']);

        $pickupCourierId = $request->get('pickupOrderSettingsId');
        if ($pickupCourierId == null)
            return new JsonResponse(['success' => false]);

        $pickupCourier = $this->commandBus->handle(new AddPickupCourierCommand($request, (int)Context::getContext()->shop->id));

        if ($pickupCourier['success']) {
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
        }

        return new JsonResponse($pickupCourier);
    }

    public function deletePickupCourier(Request $request)
    {
        $pickupCourierId = $request->get('pickupOrderId');
        if ($pickupCourierId == null) {
            $this->flashErrors([$this->trans('It is not possible to cancel the collection of a courier shipment', 'Modules.Dpdshipping.Admin')]);
            return $this->redirectToRoute('dpdshipping_pickup_courier_form');
        }

        $pickupCourier = $this->commandBus->handle(new CancelPickupCourierCommand($pickupCourierId, (int)Context::getContext()->shop->id));

        if ($pickupCourier['success']) {
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
        } else {
            if (!empty($pickupCourier['errors']))
                $this->flashErrors($pickupCourier['errors']);
        }

        return $this->redirectToRoute('dpdshipping_pickup_courier_form');
    }

    public function getPickupCourierTimeFramesAjax(Request $request): JsonResponse
    {
        if (!ValidateUserUtil::validateEmployeeSession($request))
            return new JsonResponse(['success' => false, 'data' => 'INVALID TOKEN']);

        $pickupCourierId = $request->get('pickupOrderSettingsId');
        $countryCode = $request->get('countryCode');
        $postalCode = $request->get('postalCode');
        if ($pickupCourierId == null)
            return new JsonResponse(['success' => false]);

        $countryIsoCode = $this->queryBus->handle(new GetCountryIsoCode($countryCode));
        $senderPlace = (new SenderPlaceV1())
            ->withCountryCode($countryIsoCode)
            ->withZipCode(str_replace('-', '', $postalCode));

        $result = $this->queryBus->handle(new GetCourierOrderAvailability($senderPlace, (int)Context::getContext()->shop->id));


        return new JsonResponse($result);
    }

}
