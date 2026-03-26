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

namespace DpdShipping\Form\Configuration\Connection;

if (!defined('_PS_VERSION_')) {
    exit;
}

use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration;
use DpdShipping\Domain\Configuration\Connection\Command\SaveConnectionCommand;
use DpdShipping\Domain\Configuration\Connection\Query\GetConnection;
use DpdShipping\Domain\Configuration\Payer\Command\AddPayerCommand;
use DpdShipping\Domain\Configuration\Payer\Query\GetPayerList;
use DpdShipping\Domain\TestConnection\Query\TestDpdConnection;
use DpdShipping\Form\CommonFormDataProvider;
use DpdShipping\Util\ArrayUtil;
use PhpEncryption;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Shop;
use Tools;
use Context;

class DpdShippingConnectionFormDataProvider extends CommonFormDataProvider implements FormDataProviderInterface
{
    public function __construct(CommandBusInterface $queryBus, CommandBusInterface $commandBus)
    {
        parent::__construct($queryBus, $commandBus);
    }

    public function getData(): array
    {
        $connectionId = Tools::getValue('connectionId');

        $idShop = Shop::getContextListShopID();

        if (empty($connectionId)) {
            return [
                'id' => null,
                'name' => "DPD API",
                'payerList' => [],
                'idShop' => $idShop
            ];
        }

        $connectionConfig = $this->queryBus->handle(new GetConnection($idShop, $connectionId));

        if(empty($connectionConfig))
            Tools::redirectAdmin($this->generateRouteUrl('dpdshipping_connection_form'));

        $return[] = [
            'id' => $connectionId,
            'idShop' => $idShop,
            'login' => $connectionConfig->getLogin(),
            'masterfid' => $connectionConfig->getMasterfid(),
            'environment' => $connectionConfig->getEnvironment(),
            'isDefault' => $connectionConfig->isDefault(),
            'name' => $connectionConfig->getName(),
        ];

        $return[] = ['payerList' => $this->queryBus->handle(new GetPayerList(false, $idShop, $connectionConfig->getId())) ?? []];

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

        foreach ($data['idShop'] as $idShop) {
            $connectionConfigId = $data['id'];
            if (!empty($connectionConfigId)) {
                $connectionConfig = $this->queryBus->handle(new GetConnection(null, $connectionConfigId));

                if (empty($connectionConfig) || $connectionConfig->getIdShop() != $idShop)
                    continue;
            }

            $connectionId = $this->queryBus->handle(new SaveConnectionCommand($connectionConfigId, $idShop, $data['name'], $data['login'], $phpEncryption->encrypt($data['password']), $data['masterfid'], $data['environment'], $data['isDefault']));

            $this->commandBus->handle(new AddPayerCommand($data['payerList'], $idShop, $connectionId));
        }

        return $errors;
    }

    private function generateRouteUrl(string $routeName): string
    {
        $context = Context::getContext();
        if ($context && $context->controller && method_exists($context->controller, 'getContainer')) {
            $container = $context->controller->getContainer();
            if ($container && $container->has('router')) {
                $router = $container->get('router');
                try {
                    return $router->generate($routeName);
                } catch (\Throwable $e) {
                    // fallback below
                }
            }
        }

        return $context->link->getAdminLink('AdminModules', true, [], ['route' => $routeName]);
    }
}
