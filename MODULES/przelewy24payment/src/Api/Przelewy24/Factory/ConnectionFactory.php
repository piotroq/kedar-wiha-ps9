<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Api\Przelewy24\Factory;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Connection\Connection;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\AccountNotFoundApiException;
use Przelewy24\Api\Przelewy24\Factory\Exceptions\ConnectionFailedApiException;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\CredentialsConfigurationProvider;
use Przelewy24\Translator\Adapter\Translator;
use Psr\Log\LoggerInterface;

class ConnectionFactory
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    private $connections = [];

    public function __construct(Translator $translator, LoggerInterface $logger = null)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param $idShop
     * @param $idCurrency
     *
     * @return Connection
     *
     * @throws \Exception
     */
    public function factory(Przlewy24AccountModel $account)
    {
        $credentialsConfig = $this->_getCredentials($account);
        $connection = $this->_getConnection($credentialsConfig);

        return $connection;
    }

    private function _getCredentials($model)
    {
        $credentialsProvider = new CredentialsConfigurationProvider();
        $credentialsConfig = $credentialsProvider->getConfiguration($model);
        if (!$credentialsConfig->getIdAccount()) {
            throw new AccountNotFoundApiException('Account not found', $this->translator->trans('Account not found', [], 'Modules.Przelewy24payment.Exception'));
        }

        return $credentialsConfig;
    }

    private function _createAndTestConnection($credentialsConfig)
    {
        $connection = new Connection(
            (string) $credentialsConfig->getIdMerchant(),
            (string) $credentialsConfig->getApiKey(),
            (bool) $credentialsConfig->getTestMode(),
            $this->logger
        );
        $response = $connection->testConnection();
        if ($response->getStatus() != 200) {
            throw new ConnectionFailedApiException('Connection failed', $this->translator->trans('Account not found', [], 'Modules.Przelewy24payment.Exception'));
        }

        return $connection;
    }

    private function _getConnection($credentialsConfig)
    {
        if (isset($this->connections[$this->_getKey($credentialsConfig)])) {
            return $this->connections[$this->_getKey($credentialsConfig)];
        }

        $connection = $this->_createAndTestConnection($credentialsConfig);
        $this->connections[$this->_getKey($credentialsConfig)] = $connection;

        return $connection;
    }

    private function _getKey($credentialsConfig)
    {
        return (string) $credentialsConfig->getIdMerchant() . '_' . (string) $credentialsConfig->getApiKey() . '_' . (string) $credentialsConfig->getTestMode();
    }
}
