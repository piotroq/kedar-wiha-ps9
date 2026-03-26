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
use DpdShipping\Entity\DpdshippingConnection;
use DpdShipping\Entity\DpdshippingPayer;
use Psr\Log\LoggerInterface;
use Throwable;

class DpdshippingConnectionRepository extends EntityRepository
{
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function findOneByIdShop($idConnection, $idShop)
    {
        $criteria = [];

        if ($idShop !== null) {
            $criteria['idShop'] = $idShop;
        }

        if ($idConnection !== null) {
            $criteria['id'] = $idConnection;
        }

        if (empty($criteria)) {
            $criteria['idShop'] = (int) Context::getContext()->shop->id;
        }

        return $this->findOneBy($criteria, ['isDefault' => 'DESC']);
    }

    public function addOrUpdate($id, $idShop, $name, $login, $password, $masterFid, $environment, $isDefault)
    {
        $em = $this->_em;
        $conn = $em->getConnection();

        $conn->beginTransaction();
        try {
            $entity = $this->findOneBy(['id' => $id]);

            $now = new DateTime();

            if (!$entity) {
                $entity = new DpdshippingConnection();
                $entity
                    ->setIdShop($idShop)
                    ->setName($name)
                    ->setLogin($login)
                    ->setPassword($password)
                    ->setMasterFid($masterFid)
                    ->setEnvironment($environment)
                    ->setDefault((bool)$isDefault)
                    ->setDateAdd($now)
                    ->setDateUpd($now);

                $em->persist($entity);
                $em->flush();
                $currentId = $entity->getId();
            } else {
                $entity
                    ->setIdShop($idShop)
                    ->setName($name)
                    ->setLogin($login)
                    ->setPassword($password)
                    ->setMasterFid($masterFid)
                    ->setEnvironment($environment)
                    ->setDefault((bool)$isDefault)
                    ->setDateUpd($now);

                $em->persist($entity);
                $em->flush();
                $currentId = (int)$id;
            }

            if ($isDefault) {
                $em->createQueryBuilder()
                    ->update(DpdshippingConnection::class, 'c')
                    ->set('c.isDefault', ':false')
                    ->where('c.idShop = :idShop')
                    ->andWhere('c.id != :id')
                    ->setParameter('false', false)
                    ->setParameter('idShop', $idShop)
                    ->setParameter('id', $currentId)
                    ->getQuery()
                    ->execute();
            }

            $conn->commit();
            return $currentId;

        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function deleteById($id): bool
    {
        $entity = $this->findOneBy(['id' => $id]);

        if (!$entity) {
            return false;
        }

        $payers = $this->_em->getRepository(DpdshippingPayer::class)
            ->findBy(['idConnection' => $id]);

        foreach ($payers as $payer) {
            $this->_em->remove($payer);
        }

        $this->_em->remove($entity);

        $this->_em->flush();

        return true;
    }

    public function findConnectionList($idShop): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.idShop in ( :idShop)')
            ->setParameter('idShop', $idShop)
            ->orderBy('c.isDefault', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
