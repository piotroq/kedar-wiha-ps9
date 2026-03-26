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

namespace DpdShipping\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use DateTime;
use Doctrine\ORM\EntityRepository;
use DpdShipping\Entity\DpdshippingPayer;
use Psr\Log\LoggerInterface;
use Shop;

class DpdshippingPayerRepository extends EntityRepository
{
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addOrUpdate(array $payerList, $idShop, $connectionId): bool
    {
        $toDelete = [];

        $currentList = $this->findAllActive(false, $idShop, $connectionId);

        foreach ($currentList as $item) {
            $toDelete[] = $item;
        }

        foreach ($toDelete as $item) {
            $recordToDelete = $this->find($item);
            $this->getEntityManager()->remove($recordToDelete);
        }
        $this->_em->flush();

        $defaultSet = false;
        foreach ($payerList as $payer) {
            $isDefault = false;
            if ($payer->isDefault() && !$defaultSet) {
                $isDefault = true;
                $defaultSet = true;
            }

            $newEntity = new DpdshippingPayer();
            $newEntity
                ->setIdShop($idShop)
                ->setIdConnection($connectionId)
                ->setName($payer->getName())
                ->setFid((string)$payer->getFid())
                ->setDefault($isDefault)
                ->setDateAdd(new DateTime())
                ->setDateUpd(new DateTime());

            $this->_em->persist($newEntity);
        }

        $this->_em->flush();

        return true;
    }

    public function findAllActive(bool $defaultFirst, $idShop, $idConnection): array
    {
        $criteria = [];

        if ($idShop !== null) {
            $criteria['idShop'] = $idShop;
        }

        if ($idConnection !== null) {
            $criteria['idConnection'] = $idConnection;
        }

        $orderBy = $defaultFirst ? ['isDefault' => 'DESC'] : [];

        return $this->findBy($criteria, $orderBy);
    }

    public function findDefault($idConnection): ?DpdshippingPayer
    {
        return $this->findOneBy(['idShop' => (int)Context::getContext()->shop->id, 'isDefault' => true, 'idConnection' => $idConnection]);
    }
}
