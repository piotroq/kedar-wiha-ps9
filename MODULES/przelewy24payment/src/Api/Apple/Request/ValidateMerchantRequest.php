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

namespace Przelewy24\Api\Apple\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Exceptions\MissingCertificateException;
use Przelewy24\Model\Dto\AppleConfig;

class ValidateMerchantRequest
{
    public const URL = 'https://apple-pay-gateway.apple.com/paymentservices/paymentSession';

    private $curl;

    private $result;

    private $error;

    private $tempFiles = [];

    public function sendRequest(AppleConfig $appleConfig)
    {
        try {
            $this->_createCurl();
            $this->_setPayload($appleConfig);
            $this->_setCertificates($appleConfig);
            $this->_setUrl();
            $this->_send();
            $this->_validateResponse();
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->result;
    }

    private function _createCurl()
    {
        $this->result = null;
        $this->error = null;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    }

    private function _setPayload(AppleConfig $appleConfig)
    {
        $payload = json_encode([
            'merchantIdentifier' => $appleConfig->getIdMerchant(),
            'displayName' => $appleConfig->getMerchantName(),
            'initiative' => 'web',
            //            'initiativeContext' => \Context::getContext()->shop->domain,
            'initiativeContext' => 'przelewy17.test.waynet.pl',
        ]);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
    }

    private function _setCertificates(AppleConfig $appleConfig)
    {
        try {
            if (empty($appleConfig->getCert()) || empty($appleConfig->getPrivateKey())) {
                throw new MissingCertificateException('Missing Apple certificate or private key');
            }
            curl_setopt($this->curl, CURLOPT_SSLCERT, $this->_createTempFile('apple_cert_', $appleConfig->getCert()));
            curl_setopt($this->curl, CURLOPT_SSLKEY, $this->_createTempFile('apple_key_', $appleConfig->getPrivateKey()));
        } catch (\Exception $e) {
            $this->cleanupTempFiles();
            throw $e;
        }
    }

    private function _createTempFile($tempName, $content)
    {
        $tempFile = tempnam(sys_get_temp_dir(), $tempName);
        if ($tempFile === false) {
            throw new \RuntimeException('Cannot create temporary file for certificate');
        }
        $this->tempFiles[] = $tempFile;
        file_put_contents($tempFile, $content);
        chmod($tempFile, 0600);

        return $tempFile;
    }

    private function cleanupTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    public function __destruct()
    {
        $this->cleanupTempFiles();
    }

    private function _setUrl()
    {
        curl_setopt($this->curl, CURLOPT_URL, self::URL);
    }

    private function _send()
    {
        try {
            $this->result = curl_exec($this->curl);
            $this->error = curl_error($this->curl);
        } finally {
            curl_close($this->curl);
            $this->cleanupTempFiles();
        }
    }

    private function _validateResponse()
    {
        if ($this->result === false) {
            throw new \RuntimeException('Error executing request: ' . $this->error);
        }
        $response = json_decode($this->result, true);
        if ($response === null) {
            throw new \RuntimeException('Invalid response format');
        }
        if (isset($response['error'])) {
            throw new \RuntimeException('Apple validation failed: ' . $response['error']);
        }
        if (!isset($response['merchantSessionIdentifier'])) {
            throw new \RuntimeException('Missing merchant identifier in response');
        }
    }
}
