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

namespace DpdShipping\Form\Configuration\Carrier;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use DpdShipping\Config\Config;
use DpdShipping\Domain\Configuration\Carrier\Command\AddCarrierPickupCommand;
use DpdShipping\Domain\Configuration\Carrier\DpdCarrier;
use DpdShipping\Domain\Configuration\Carrier\DpdCarrierPrestashopConfiguration;
use DpdShipping\Domain\Configuration\Carrier\Query\GetCarrier;
use DpdShipping\Domain\Configuration\Carrier\Query\GetCarrierPickup;
use DpdShipping\Domain\Configuration\Carrier\Query\GetCodPaymentModules;
use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration;
use DpdShipping\Form\CommonFormDataProvider;
use Module;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Shop;

class DpdShippingCarrierFormDataProvider extends CommonFormDataProvider implements FormDataProviderInterface
{
    private $dpdCarrier;
    private $translator;

    public function __construct(CommandBusInterface $queryBus, CommandBusInterface $commandBus, DpdCarrier $dpdCarrier, $translator)
    {
        parent::__construct($queryBus, $commandBus);
        $this->dpdCarrier = $dpdCarrier;
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function getData(): array
    {
        $return = [];
        $idShop = (int)Context::getContext()->shop->id;
        $standardCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_STANDARD, $idShop));
        $return['dpdPolandCarrierStandard'] = isset($standardCarrier) && $standardCarrier !== false;

        $standardCodCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_STANDARD_COD, $idShop));
        $return['dpdPolandCarrierStandardCod'] = isset($standardCodCarrier) && $standardCodCarrier !== false;

        $swipBoxCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_SWIP_BOX, $idShop));
        $return['dpdPolandCarrierSwipBox'] = isset($swipBoxCarrier) && $swipBoxCarrier !== false;

        $swipBoxCarrierFilter = $this->queryBus->handle(new GetCarrierPickup(Config::DPD_SWIP_BOX));

        $return['dpdPolandCarrierSwipBoxFilterOpenLate'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterOpenLate');
        $return['dpdPolandCarrierSwipBoxFilterOpenSaturdays'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterOpenSaturdays');
        $return['dpdPolandCarrierSwipBoxFilterOpenSundays'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterOpenSundays');
        $return['dpdPolandCarrierSwipBoxFilterDisabledFriendly'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDisabledFriendly');
        $return['dpdPolandCarrierSwipBoxFilterParking'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterParking');
        $return['dpdPolandCarrierSwipBoxFilterDirectDelivery'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDirectDelivery');
        $return['dpdPolandCarrierSwipBoxFilterDirectDeliveryCod'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDirectDeliveryCod');
        $return['dpdPolandCarrierSwipBoxFilterDropoffOnline'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDropoffOnline');
        $return['dpdPolandCarrierSwipBoxFilterDropoffOffline'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDropoffOffline');
        $return['dpdPolandCarrierSwipBoxFilterSwapParcel'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterSwapParcel');
        $return['dpdPolandCarrierSwipBoxFilterFresh'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterFresh');
        $return['dpdPolandCarrierSwipBoxFilterCardPayment'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterCardPayment');
        $return['dpdPolandCarrierSwipBoxFilterFittingRoom'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterFittingRoom');
        $return['dpdPolandCarrierSwipBoxFilterRod'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterRod');
        $return['dpdPolandCarrierSwipBoxFilterLQ'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterLQ');
        $return['dpdPolandCarrierSwipBoxFilterDigitalLabel'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterDigitalLabel');
        $return['dpdPolandCarrierSwipBoxFilterSwipBox'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterSwipBox');
        $return['dpdPolandCarrierSwipBoxFilterPointsWithServices'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterPointsWithServices');
        $return['dpdPolandCarrierSwipBoxFilterHideFilters'] = $this->getPickupFilterValue($swipBoxCarrierFilter, 'dpdPolandCarrierSwipBoxFilterHideFilters');

        $pickupCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP, $idShop));
        $return['dpdPolandCarrierPickup'] = isset($pickupCarrier) && $pickupCarrier !== false;

        $pickupCodCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP_COD, $idShop));
        $return['dpdPolandCarrierPickupCOD'] = isset($pickupCodCarrier) && $pickupCodCarrier !== false;

        $pickupCarrierFilter = $this->queryBus->handle(new GetCarrierPickup(Config::DPD_PICKUP));

        $return['dpdPolandCarrierPickupFilterOpenLate'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterOpenLate');
        $return['dpdPolandCarrierPickupFilterOpenSaturdays'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterOpenSaturdays');
        $return['dpdPolandCarrierPickupFilterOpenSundays'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterOpenSundays');
        $return['dpdPolandCarrierPickupFilterDisabledFriendly'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDisabledFriendly');
        $return['dpdPolandCarrierPickupFilterParking'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterParking');
        $return['dpdPolandCarrierPickupFilterDirectDelivery'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDirectDelivery');
        $return['dpdPolandCarrierPickupFilterDirectDeliveryCod'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDirectDeliveryCod');
        $return['dpdPolandCarrierPickupFilterDropoffOnline'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDropoffOnline');
        $return['dpdPolandCarrierPickupFilterDropoffOffline'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDropoffOffline');
        $return['dpdPolandCarrierPickupFilterSwapParcel'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterSwapParcel');
        $return['dpdPolandCarrierPickupFilterFresh'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterFresh');
        $return['dpdPolandCarrierPickupFilterCardPayment'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterCardPayment');
        $return['dpdPolandCarrierPickupFilterFittingRoom'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterFittingRoom');
        $return['dpdPolandCarrierPickupFilterRod'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterRod');
        $return['dpdPolandCarrierPickupFilterLQ'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterLQ');
        $return['dpdPolandCarrierPickupFilterDigitalLabel'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterDigitalLabel');
        $return['dpdPolandCarrierPickupFilterSwipBox'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterSwipBox');
        $return['dpdPolandCarrierPickupFilterPointsWithServices'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterPointsWithServices');
        $return['dpdPolandCarrierPickupFilterHideFilters'] = $this->getPickupFilterValue($pickupCarrierFilter, 'dpdPolandCarrierPickupFilterHideFilters');

        $pickupCarrierCodFilter = $this->queryBus->handle(new GetCarrierPickup(Config::DPD_PICKUP_COD));

        $return['dpdPolandCarrierPickupCODFilterOpenLate'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterOpenLate');
        $return['dpdPolandCarrierPickupCODFilterOpenSaturdays'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterOpenSaturdays');
        $return['dpdPolandCarrierPickupCODFilterOpenSundays'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterOpenSundays');
        $return['dpdPolandCarrierPickupCODFilterDisabledFriendly'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDisabledFriendly');
        $return['dpdPolandCarrierPickupCODFilterParking'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterParking');
        $return['dpdPolandCarrierPickupCODFilterDirectDelivery'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDirectDelivery');
        $return['dpdPolandCarrierPickupCODFilterDirectDeliveryCod'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDirectDeliveryCod');
        $return['dpdPolandCarrierPickupCODFilterDropoffOnline'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDropoffOnline');
        $return['dpdPolandCarrierPickupCODFilterDropoffOffline'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDropoffOffline');
        $return['dpdPolandCarrierPickupCODFilterSwapParcel'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterSwapParcel');
        $return['dpdPolandCarrierPickupCODFilterFresh'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterFresh');
        $return['dpdPolandCarrierPickupCODFilterCardPayment'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterCardPayment');
        $return['dpdPolandCarrierPickupCODFilterFittingRoom'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterFittingRoom');
        $return['dpdPolandCarrierPickupCODFilterRod'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterRod');
        $return['dpdPolandCarrierPickupCODFilterLQ'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterLQ');
        $return['dpdPolandCarrierPickupCODFilterDigitalLabel'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterDigitalLabel');
        $return['dpdPolandCarrierPickupCODFilterSwipBox'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterSwipBox');
        $return['dpdPolandCarrierPickupCODFilterPointsWithServices'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterPointsWithServices');
        $return['dpdPolandCarrierPickupCODFilterHideFilters'] = $this->getPickupFilterValue($pickupCarrierCodFilter, 'dpdPolandCarrierPickupCODFilterHideFilters');

        $return['dpdCarrierCodPaymentMethods'] = $this->queryBus->handle(new GetCodPaymentModules(Configuration::DPD_COD_PAYMENT_METHODS)) ?? Module::getPaymentModules();

        return $return;
    }

    public function setData(array $data): array
    {
        $idShop = (int)Context::getContext()->shop->id;

        $this->dpdCarrier->handleCarrier(Config::DPD_SWIP_BOX, $this->translator->trans('DPD Poland - Swip Box', [], 'Modules.Dpdshipping.Carrier'), $data['dpdPolandCarrierSwipBox'], $idShop);

        $this->dpdCarrier->handleCarrier(Config::DPD_PICKUP, $this->translator->trans('DPD Poland - Pickup', [], 'Modules.Dpdshipping.Carrier'), $data['dpdPolandCarrierPickup'], $idShop);
        $this->dpdCarrier->handleCarrier(Config::DPD_PICKUP_COD, $this->translator->trans('DPD Poland - Pickup COD', [], 'Modules.Dpdshipping.Carrier'), $data['dpdPolandCarrierPickupCOD'], $idShop);

        $this->dpdCarrier->handleCarrier(Config::DPD_STANDARD, $this->translator->trans('DPD Poland', [], 'Modules.Dpdshipping.Carrier'), $data['dpdPolandCarrierStandard'], $idShop);
        $this->dpdCarrier->handleCarrier(Config::DPD_STANDARD_COD, $this->translator->trans('DPD Poland - COD', [], 'Modules.Dpdshipping.Carrier'), $data['dpdPolandCarrierStandardCod'], $idShop);

        $this->saveSwipBoxFilters($data, $idShop);
        $this->savePickupFilters($data, $idShop);
        $this->savePickupCodFilters($data, $idShop);

        if (isset($data['dpdCarrierCodPaymentMethods'])) {
            $this->saveConfiguration(Configuration::DPD_COD_PAYMENT_METHODS, json_encode($data['dpdCarrierCodPaymentMethods'], $idShop));
        }

        return [];
    }

    /**
     * @param array $data
     * @return void
     */
    public function saveSwipBoxFilters(array $data, $idShop): void
    {
        $swipBoxFilters = [
            'dpdPolandCarrierSwipBoxFilterOpenLate' => $data['dpdPolandCarrierSwipBoxFilterOpenLate'],
            'dpdPolandCarrierSwipBoxFilterOpenSaturdays' => $data['dpdPolandCarrierSwipBoxFilterOpenSaturdays'],
            'dpdPolandCarrierSwipBoxFilterOpenSundays' => $data['dpdPolandCarrierSwipBoxFilterOpenSundays'],
            'dpdPolandCarrierSwipBoxFilterDisabledFriendly' => $data['dpdPolandCarrierSwipBoxFilterDisabledFriendly'],
            'dpdPolandCarrierSwipBoxFilterParking' => $data['dpdPolandCarrierSwipBoxFilterParking'],
            'dpdPolandCarrierSwipBoxFilterDirectDelivery' => $data['dpdPolandCarrierSwipBoxFilterDirectDelivery'],
            'dpdPolandCarrierSwipBoxFilterDirectDeliveryCod' => $data['dpdPolandCarrierSwipBoxFilterDirectDeliveryCod'],
            'dpdPolandCarrierSwipBoxFilterDropoffOnline' => $data['dpdPolandCarrierSwipBoxFilterDropoffOnline'],
            'dpdPolandCarrierSwipBoxFilterDropoffOffline' => $data['dpdPolandCarrierSwipBoxFilterDropoffOffline'],
            'dpdPolandCarrierSwipBoxFilterSwapParcel' => $data['dpdPolandCarrierSwipBoxFilterSwapParcel'],
            'dpdPolandCarrierSwipBoxFilterFresh' => $data['dpdPolandCarrierSwipBoxFilterFresh'],
            'dpdPolandCarrierSwipBoxFilterFittingRoom' => $data['dpdPolandCarrierSwipBoxFilterFittingRoom'],
            'dpdPolandCarrierSwipBoxFilterCardPayment' => $data['dpdPolandCarrierSwipBoxFilterCardPayment'],
            'dpdPolandCarrierSwipBoxFilterRod' => $data['dpdPolandCarrierSwipBoxFilterRod'],
            'dpdPolandCarrierSwipBoxFilterLQ' => $data['dpdPolandCarrierSwipBoxFilterLQ'],
            'dpdPolandCarrierSwipBoxFilterDigitalLabel' => $data['dpdPolandCarrierSwipBoxFilterDigitalLabel'],
            'dpdPolandCarrierSwipBoxFilterSwipBox' => true,
            'dpdPolandCarrierSwipBoxFilterPointsWithServices' => $data['dpdPolandCarrierSwipBoxFilterPointsWithServices'],
            'dpdPolandCarrierSwipBoxFilterHideFilters' => true,
        ];
        $carrier = $this->queryBus->handle(new GetCarrier(Config::DPD_SWIP_BOX, $idShop));
        if ($carrier !== false) {
            $filters = [];
            foreach ($swipBoxFilters as $key => $value) {
                $this->commandBus->handle(new AddCarrierPickupCommand($carrier->id, $key, $value));
                if ($value == 1) {
                    $urlFilter = $this->translateToUrlFilter($key);
                    if (!empty($urlFilter)) {
                        $filters[] = $urlFilter;
                    }
                }
            }
            $pudoMapUrl = Config::PICKUP_MAP_BASE_URL . '&' . implode('&', $filters);
            DpdCarrierPrestashopConfiguration::setConfig(Config::DPD_SWIP_BOX_MAP_URL_WITH_FILTERS, $pudoMapUrl, $idShop);
        }
    }
    /**
     * @param array $data
     * @return void
     */
    public function savePickupFilters(array $data, $idShop): void
    {
        $pickupFilters = [
            'dpdPolandCarrierPickupFilterOpenLate' => $data['dpdPolandCarrierPickupFilterOpenLate'],
            'dpdPolandCarrierPickupFilterOpenSaturdays' => $data['dpdPolandCarrierPickupFilterOpenSaturdays'],
            'dpdPolandCarrierPickupFilterOpenSundays' => $data['dpdPolandCarrierPickupFilterOpenSundays'],
            'dpdPolandCarrierPickupFilterDisabledFriendly' => $data['dpdPolandCarrierPickupFilterDisabledFriendly'],
            'dpdPolandCarrierPickupFilterParking' => $data['dpdPolandCarrierPickupFilterParking'],
            'dpdPolandCarrierPickupFilterDirectDelivery' => $data['dpdPolandCarrierPickupFilterDirectDelivery'],
            'dpdPolandCarrierPickupFilterDirectDeliveryCod' => $data['dpdPolandCarrierPickupFilterDirectDeliveryCod'],
            'dpdPolandCarrierPickupFilterDropoffOnline' => $data['dpdPolandCarrierPickupFilterDropoffOnline'],
            'dpdPolandCarrierPickupFilterDropoffOffline' => $data['dpdPolandCarrierPickupFilterDropoffOffline'],
            'dpdPolandCarrierPickupFilterSwapParcel' => $data['dpdPolandCarrierPickupFilterSwapParcel'],
            'dpdPolandCarrierPickupFilterFresh' => $data['dpdPolandCarrierPickupFilterFresh'],
            'dpdPolandCarrierPickupFilterFittingRoom' => $data['dpdPolandCarrierPickupFilterFittingRoom'],
            'dpdPolandCarrierPickupFilterCardPayment' => $data['dpdPolandCarrierPickupFilterCardPayment'],
            'dpdPolandCarrierPickupFilterRod' => $data['dpdPolandCarrierPickupFilterRod'],
            'dpdPolandCarrierPickupFilterLQ' => $data['dpdPolandCarrierPickupFilterLQ'],
            'dpdPolandCarrierPickupFilterDigitalLabel' => $data['dpdPolandCarrierPickupFilterDigitalLabel'],
            'dpdPolandCarrierPickupFilterSwipBox' => $data['dpdPolandCarrierPickupFilterSwipBox'],
            'dpdPolandCarrierPickupFilterPointsWithServices' => $data['dpdPolandCarrierPickupFilterPointsWithServices'],
            'dpdPolandCarrierPickupFilterHideFilters' => false,
        ];
        $carrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP, $idShop));
        if ($carrier !== false) {
            $filters = [];
            foreach ($pickupFilters as $key => $value) {
                $this->commandBus->handle(new AddCarrierPickupCommand($carrier->id, $key, $value));
                if ($value == 1) {
                    $urlFilter = $this->translateToUrlFilter($key);
                    if (!empty($urlFilter)) {
                        $filters[] = $urlFilter;
                    }
                }
            }
            $pudoMapUrl = Config::PICKUP_MAP_BASE_URL . '&' . implode('&', $filters);
            DpdCarrierPrestashopConfiguration::setConfig(Config::DPD_PICKUP_MAP_URL_WITH_FILTERS, $pudoMapUrl, $idShop);
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function savePickupCodFilters(array $data, $idShop): void
    {
        $pickupFilters = [
            'dpdPolandCarrierPickupCODFilterOpenLate' => $data['dpdPolandCarrierPickupCODFilterOpenLate'],
            'dpdPolandCarrierPickupCODFilterOpenSaturdays' => $data['dpdPolandCarrierPickupCODFilterOpenSaturdays'],
            'dpdPolandCarrierPickupCODFilterOpenSundays' => $data['dpdPolandCarrierPickupCODFilterOpenSundays'],
            'dpdPolandCarrierPickupCODFilterDisabledFriendly' => $data['dpdPolandCarrierPickupCODFilterDisabledFriendly'],
            'dpdPolandCarrierPickupCODFilterParking' => $data['dpdPolandCarrierPickupCODFilterParking'],
            'dpdPolandCarrierPickupCODFilterDirectDelivery' => $data['dpdPolandCarrierPickupCODFilterDirectDelivery'],
            'dpdPolandCarrierPickupCODFilterDirectDeliveryCod' => true,
            'dpdPolandCarrierPickupCODFilterDropoffOnline' => $data['dpdPolandCarrierPickupCODFilterDropoffOnline'],
            'dpdPolandCarrierPickupCODFilterDropoffOffline' => $data['dpdPolandCarrierPickupCODFilterDropoffOffline'],
            'dpdPolandCarrierPickupCODFilterSwapParcel' => $data['dpdPolandCarrierPickupCODFilterSwapParcel'],
            'dpdPolandCarrierPickupCODFilterFresh' => $data['dpdPolandCarrierPickupCODFilterFresh'],
            'dpdPolandCarrierPickupCODFilterFittingRoom' => $data['dpdPolandCarrierPickupCODFilterFittingRoom'],
            'dpdPolandCarrierPickupCODFilterCardPayment' => $data['dpdPolandCarrierPickupCODFilterCardPayment'],
            'dpdPolandCarrierPickupCODFilterRod' => $data['dpdPolandCarrierPickupCODFilterRod'],
            'dpdPolandCarrierPickupCODFilterLQ' => $data['dpdPolandCarrierPickupCODFilterLQ'],
            'dpdPolandCarrierPickupCODFilterDigitalLabel' => $data['dpdPolandCarrierPickupCODFilterDigitalLabel'],
            'dpdPolandCarrierPickupCODFilterSwipBox' => $data['dpdPolandCarrierPickupCODFilterSwipBox'],
            'dpdPolandCarrierPickupCODFilterPointsWithServices' => $data['dpdPolandCarrierPickupCODFilterPointsWithServices'],
            'dpdPolandCarrierPickupCODFilterHideFilters' => false,
        ];

        $carrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP_COD, $idShop));
        if ($carrier !== false) {
            $filters = [];
            foreach ($pickupFilters as $key => $value) {
                $this->commandBus->handle(new AddCarrierPickupCommand($carrier->id, $key, $value));
                if ($value == 1) {
                    $urlFilter = $this->translateToUrlFilter($key);
                    if (!empty($urlFilter)) {
                        $filters[] = $urlFilter;
                    }
                }
            }
            $pudoMapUrl = Config::PICKUP_MAP_BASE_URL . '&' . implode('&', $filters);
            DpdCarrierPrestashopConfiguration::setConfig(Config::DPD_PICKUP_COD_MAP_URL_WITH_FILTERS, $pudoMapUrl, $idShop);
        }
    }

    private function getPickupFilterValue($filterArray, $filterName): bool
    {
        foreach ($filterArray as $object) {
            if ($object->getName() === $filterName) {
                return (bool)$object->getValue();
            }
        }

        return false;
    }

    private function translateToUrlFilter(string $key)
    {
        $search = str_replace(['dpdPolandCarrierPickupCOD', 'dpdPolandCarrierPickup', 'dpdPolandCarrierSwipBox'], ['', ''], $key);

        $pickupFilters = [
            'FilterOpenLate' => 'open_late',
            'FilterOpenSaturdays' => 'open_saturdays',
            'FilterOpenSundays' => 'open_sundays',
            'FilterDisabledFriendly' => 'disabled_friendly',
            'FilterParking' => 'parking',
            'FilterDirectDelivery' => 'direct_delivery',
            'FilterDirectDeliveryCod' => 'direct_delivery_cod',
            'FilterDropoffOnline' => 'dropoff_online',
            'FilterDropoffOffline' => 'dropoff_offline',
            'FilterSwapParcel' => 'swap_parcel',
            'FilterFresh' => 'd_fresh',
            'FilterFittingRoom' => 'fitting_room',
            'FilterCardPayment' => 'card_payment',
            'FilterRod' => 'rod',
            'FilterLQ' => 'dpd_lq',
            'FilterDigitalLabel' => 'digital_label',
            'FilterSwipBox' => 'swip_box',
            'FilterPointsWithServices' => 'points_with_services',
            'FilterHideFilters' => 'hideFilters',
        ];

        return $pickupFilters[$search] . '=1' ?? '';
    }
}
