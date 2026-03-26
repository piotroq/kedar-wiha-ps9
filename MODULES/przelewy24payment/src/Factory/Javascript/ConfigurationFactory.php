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

namespace Przelewy24\Factory\Javascript;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Factory\Exceptions\AccountNotFoundApiException;
use Przelewy24\Dto\Javascript\Config;
use Przelewy24\Model\Dto\CredentialsConfig;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\CredentialsConfigurationProvider;

class ConfigurationFactory
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var CredentialsConfigurationProvider
     */
    private $credentialsConfigurationProvider;

    /**
     * @var Przlewy24AccountModel|null
     */
    private $model;

    /**
     * @var CredentialsConfig
     */
    private $credentialsConfig;

    public function __construct(\Context $context, CredentialsConfigurationProvider $credentialsConfigurationProvider)
    {
        $this->context = $context;
        $this->credentialsConfigurationProvider = $credentialsConfigurationProvider;
    }

    public function factory(): Config
    {
        $this->_init();

        return $this->_fillConfig();
    }

    protected function _init()
    {
        $this->model = Przlewy24AccountModel::getAccountByIDCurrencyAndIdShop($this->context->currency->id, $this->context->shop->id);
        if (empty($this->model)) {
            throw new AccountNotFoundApiException('Przelewy24 account not found');
        }
        $this->credentialsConfig = $this->credentialsConfigurationProvider->getConfiguration($this->model);
    }

    protected function _fillConfig()
    {
        $sessionID = hash('sha224', rand() . time());
        $mode = $this->credentialsConfig->getTestMode() ? 'sandbox' : 'secure';
        $config = new Config();
        $config->setLang($this->context->language->iso_code);
        $config->setMode($mode);
        $config->setMerchantId((int) $this->credentialsConfig->getIdMerchant());
        $config->setSessionId($sessionID);
        $config->setSign($this->calculateSign($config->getMerchantId(), $config->getSessionId(), $this->credentialsConfig->getSalt()));

        return $config;
    }

    private function calculateSign($merchantId, $sessionId, $crc)
    {
        $params = [
            'merchantId' => (int) $merchantId,
            'sessionId' => (string) $sessionId,
            'crc' => (string) $crc,
        ];

        $jsonData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $jsonData);

        return $sign;
    }
}
