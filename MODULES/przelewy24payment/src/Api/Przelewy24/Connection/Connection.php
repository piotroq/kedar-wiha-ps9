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

namespace Przelewy24\Api\Przelewy24\Connection;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Request\Interfaces\PrzelewyRequestInterface;
use Przelewy24\Api\Przelewy24\Request\TestAccessRequest;
use Przelewy24\Api\Przelewy24\Response\PrzelewyResposne;
use Psr\Log\LoggerInterface;

class Connection
{
    public const SANDBOX = 'https://sandbox.przelewy24.pl';

    public const PROD = 'https://secure.przelewy24.pl';

    public const API_URL = '/api/v1';

    public const TRN_URL = '/trnRequest';

    public const PANEL_URL = '/panel/transakcja.php';

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $usernmae;

    private $curl_opt_array = [];

    /**
     * @var string
     */
    private $mode;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public function __construct(string $usernmae, string $password, bool $sandbox = false, LoggerInterface $logger = null)
    {
        $this->password = $password;
        $this->usernmae = $usernmae;
        $this->mode = $sandbox ? self::SANDBOX : self::PROD;
        $this->logger = $logger;
    }

    private function _getAuthorizationHeader()
    {
        return 'Authorization: Basic ' . base64_encode($this->usernmae . ':' . $this->password);
    }

    public function testConnection()
    {
        $request = new TestAccessRequest();

        return $this->sendRequest($request);
    }

    public function sendRequest(PrzelewyRequestInterface $request)
    {
        $this->_resetCurlOptArray();
        $this->_setDataFromRequest($request);

        if ($this->logger) {
            $this->logger->info('Przelewy24 Request', [
                'request' => [
                    'method' => $request->getMethod(),
                    'url' => $this->mode . self::API_URL . '/' . $request->getUrl(),
                    'body' => $request->getBody() ? json_decode(json_encode($request->getBody()), true) : null,
                    'headers' => $request->getHeaders(),
                ],
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $this->curl_opt_array);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($this->logger) {
            $this->logger->info('Przelewy24 Response', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }

        return $this->_getResponse($response, $httpCode);
    }

    private function _getResponse($response, $status)
    {
        $responseObject = new PrzelewyResposne();
        $responseObject->setStatus($status);
        $responseArray = json_decode($response, true);
        foreach ($responseArray as $key => $value) {
            if ($key == 'status') {
                continue;
            }
            $setter = 'set' . ucfirst($key);
            if (is_callable([$responseObject, $setter])) {
                $responseObject->{$setter}($value);
            }
        }

        return $responseObject;
    }

    private function _setDataFromRequest(PrzelewyRequestInterface $request)
    {
        $this->_setUrl($request->getUrl());
        $this->_setMethod($request->getMethod());
        $this->_setBody($request->getBody());
        $this->_setHeaders($request->getHeaders());
    }

    private function _setUrl(string $url)
    {
        $this->curl_opt_array[CURLOPT_URL] = $this->mode . self::API_URL . '/' . $url;
    }

    private function _setMethod(string $method)
    {
        $this->curl_opt_array[CURLOPT_CUSTOMREQUEST] = $method;
    }

    private function _setBody(?PrzelewyBodyInterface $body)
    {
        if ($body) {
            $this->curl_opt_array[CURLOPT_POSTFIELDS] = json_encode($body);
        }
    }

    private function _setHeaders(array $headers)
    {
        $headers[] = $this->_getAuthorizationHeader();
        $this->curl_opt_array[CURLOPT_HTTPHEADER] = $headers;
    }

    private function _resetCurlOptArray()
    {
        $this->curl_opt_array = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
    }

    public function getUrlTrnRequest(string $token)
    {
        return $this->mode . self::TRN_URL . '/' . $token;
    }
}
