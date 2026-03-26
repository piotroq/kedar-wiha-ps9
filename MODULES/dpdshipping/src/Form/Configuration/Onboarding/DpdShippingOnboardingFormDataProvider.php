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

namespace DpdShipping\Form\Configuration\Onboarding;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use DpdShipping\Config\Config;
use DpdShipping\Domain\Configuration\Carrier\DpdCarrier;
use DpdShipping\Domain\Configuration\Carrier\Query\GetCarrier;
use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration;
use DpdShipping\Domain\Configuration\Connection\Command\SaveConnectionCommand;
use DpdShipping\Domain\Configuration\Connection\Query\GetConnection;
use DpdShipping\Domain\Configuration\Payer\Command\AddPayerCommand;
use DpdShipping\Domain\Configuration\Payer\Query\GetDefaultPayer;
use DpdShipping\Domain\Configuration\SenderAddress\Command\AddSenderAddressCommand;
use DpdShipping\Domain\Configuration\SenderAddress\Query\GetSenderAddressList;
use DpdShipping\Domain\TestConnection\Query\TestDpdConnection;
use DpdShipping\Entity\DpdshippingPayer;
use DpdShipping\Entity\DpdshippingSenderAddress;
use DpdShipping\Form\CommonFormDataProvider;
use DpdShipping\Util\ArrayUtil;
use PhpEncryption;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Shop;

class DpdShippingOnboardingFormDataProvider extends CommonFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DpdCarrier
     */
    private $dpdCarrier;

    public function __construct(CommandBusInterface $queryBus, CommandBusInterface $commandBus, DpdCarrier $dpdCarrier)
    {
        parent::__construct($queryBus, $commandBus);
        $this->dpdCarrier = $dpdCarrier;
    }

    public function getData(): array
    {
        $idShop = (int)Context::getContext()->shop->id;
        $connectionConfig = $this->queryBus->handle(new GetConnection($idShop, null));

        if(!isset($connectionConfig) && $connectionConfig != null) {
            $return[] = [
                'login' => $connectionConfig->getLogin(),
                'masterfid' => $connectionConfig->getMasterfid(),
                'environment' => $connectionConfig->getEnvironment(),
            ];

            $defaultPayer = $this->queryBus->handle(new GetDefaultPayer($connectionConfig->getId()));
            if (isset($defaultPayer)) {
                $return[] = ['defaultFidNumber' => $defaultPayer->getFid()];
            }
        }

        $senderAddress = $this->getSenderAddress($idShop);

        if (isset($senderAddress)) {
            $return[] = [
                'senderAddressId' => $senderAddress->getId(),
                'alias' => $senderAddress->getAlias(),
                'company' => $senderAddress->getCompany(),
                'name' => $senderAddress->getName(),
                'street' => $senderAddress->getStreet(),
                'city' => $senderAddress->getCity(),
                'country' => $senderAddress->getCountryCode(),
                'postcode' => $senderAddress->getPostalCode(),
                'mail' => $senderAddress->getMail(),
                'phone' => $senderAddress->getPhone(),
            ];
        }

        $swipBoxCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_SWIP_BOX, $idShop));
        $pickupCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP, $idShop));
        $pickupCodCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_PICKUP_COD, $idShop));
        $standardCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_STANDARD, $idShop));
        $standardCodCarrier = $this->queryBus->handle(new GetCarrier(Config::DPD_STANDARD_COD, $idShop));

        $return[] = [
            'carrierDpdPolandSwipBox' => isset($swipBoxCarrier) && $swipBoxCarrier !== false,
            'carrierDpdPolandPickup' => isset($pickupCarrier) && $pickupCarrier !== false,
            'carrierDpdPolandPickupCod' => isset($pickupCodCarrier) && $pickupCodCarrier !== false,
            'carrierDpdPoland' => isset($standardCarrier) && $standardCarrier !== false,
            'carrierDpdPolandCod' => isset($standardCodCarrier) && $standardCodCarrier !== false,
        ];

        return ArrayUtil::flatArray($return);
    }

    public function setData(array $data): array
    {
        $errors = [];

        $testConnection = $this->queryBus->handle(new TestDpdConnection($data['login'], $data['password'], $data['masterfid'], $data['environment']));

        if ($testConnection !== true) {
            $errors[] = $testConnection;

            return $errors;
        }

        $phpEncryption = new PhpEncryption(_NEW_COOKIE_KEY_);
        foreach (Shop::getContextListShopID() as $idShop) {
            $payer = new DpdshippingPayer();
            $payer
                ->setName('FID')
                ->setFid((string)$data['defaultFidNumber'])
                ->setDefault(true);
            $connectionId = $this->queryBus->handle(new SaveConnectionCommand(null, $idShop, "DPD API", $data['login'], $phpEncryption->encrypt($data['password']), $data['masterfid'], $data['environment'], true));
            $this->commandBus->handle(new AddPayerCommand([$payer], $idShop, $connectionId));

            $this->saveConfiguration(Configuration::DEFAULT_PARAM_WEIGHT, 1.0, $idShop);

            $this->setSenderAddress($data, $idShop);
            $this->dpdCarrier->handleCarrier(Config::DPD_SWIP_BOX, 'DPD Poland - Swip Box', $data['carrierDpdPolandSwipBox'], $idShop);
            $this->dpdCarrier->handleCarrier(Config::DPD_PICKUP, 'DPD Poland - Pickup', $data['carrierDpdPolandPickup'], $idShop);
            $this->dpdCarrier->handleCarrier(Config::DPD_PICKUP_COD, 'DPD Poland - Pickup COD', $data['carrierDpdPolandPickupCod'], $idShop);

            $this->dpdCarrier->handleCarrier(Config::DPD_STANDARD, 'DPD Poland', $data['carrierDpdPoland'], $idShop);
            $this->dpdCarrier->handleCarrier(Config::DPD_STANDARD_COD, 'DPD Poland - COD', $data['carrierDpdPolandCod'], $idShop);

            $this->saveConfiguration(Configuration::NEED_ONBOARDING, '0', $idShop);
        }

        return $errors;
    }

    private function getSenderAddress($idShop): ?DpdshippingSenderAddress
    {
        $senderAddressList = $this->queryBus->handle(new GetSenderAddressList(true, $idShop));
        if (isset($senderAddressList) && count($senderAddressList) > 0 && $senderAddressList[0]->isDefault()) {
            return $senderAddressList[0];
        }

        return null;
    }

    private function setSenderAddress(array $data, int $idShop)
    {
        $entity = new DpdshippingSenderAddress();
        $entity
            ->setIdShop($idShop)
            ->setAlias($data['alias'])
            ->setCompany($data['company'])
            ->setName($data['name'])
            ->setStreet($data['street'])
            ->setCity($data['city'])
            ->setCountryCode($data['country'])
            ->setPostalCode($data['postcode'])
            ->setMail($data['mail'])
            ->setPhone($data['phone'])
            ->setIsDefault(true);

        $this->queryBus->handle(new AddSenderAddressCommand($entity, $idShop));
    }
}
