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

namespace Przelewy24\Api\Przelewy24\Dto\Body;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Additional\Additional;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\CardData;
use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;
use Przelewy24\Dto\Session;

class TransactionRegister implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $merchantId;

    private $posId;

    private $sessionId;

    private $amount;

    private $currency;

    private $description;

    private $email;

    private $client;

    private $address;

    private $zip;

    private $city;

    private $country;

    private $phone;

    private $language;

    private $method;

    private $urlReturn;

    private $urlStatus;

    private $urlNotify;
    private $urlCardPaymentNotification;

    private $timeLimit;

    private $channel;

    private $waitForResult = false;

    private $regulationAccept = false;

    private $shipping;

    private $transferLabel;

    private $mobileLib;

    private $sdkVersion;

    private $sign;

    private $encoding;

    private $methodRefId;

    private $cart = [];

    private $cardData = [];

    private $additional = [];

    public function getAdditional(): ?Additional
    {
        return $this->additional;
    }

    /**
     * @return TransactionRegister
     */
    public function setAdditional(Additional $additional)
    {
        $this->additional = $additional;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return TransactionRegister
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return TransactionRegister
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return array
     */
    public function getCart(): ?array
    {
        return $this->cart;
    }

    /**
     * @param array $cart
     *
     * @return TransactionRegister
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return TransactionRegister
     */
    public function addProduct(Product $product)
    {
        $this->cart[] = $product;

        return $this;
    }

    public function getChannel(): ?int
    {
        return $this->channel;
    }

    /**
     * @param int $channel
     *
     * @return TransactionRegister
     */
    public function setChannel(int $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return TransactionRegister
     */
    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * @param string $client
     *
     * @return TransactionRegister
     */
    public function setClient(string $client)
    {
        $this->client = $client;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return TransactionRegister
     */
    public function setCountry(string $country)
    {
        $this->country = $country;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return TransactionRegister
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return TransactionRegister
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return TransactionRegister
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     *
     * @return TransactionRegister
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return TransactionRegister
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    /**
     * @param int $merchantId
     *
     * @return TransactionRegister
     */
    public function setMerchantId(int $merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMethod(): ?int
    {
        return $this->method;
    }

    /**
     * @param int $method
     *
     * @return TransactionRegister
     */
    public function setMethod(int $method)
    {
        $this->method = $method;

        return $this;
    }

    public function getMethodRefId(): ?string
    {
        return $this->methodRefId;
    }

    /**
     * @param string $methodRefId
     *
     * @return TransactionRegister
     */
    public function setMethodRefId(string $methodRefId)
    {
        $this->methodRefId = $methodRefId;

        return $this;
    }

    public function getMobileLib(): ?int
    {
        return $this->mobileLib;
    }

    /**
     * @param int $mobileLib
     *
     * @return TransactionRegister
     */
    public function setMobileLib(int $mobileLib)
    {
        $this->mobileLib = $mobileLib;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return TransactionRegister
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPosId(): ?int
    {
        return $this->posId;
    }

    /**
     * @param int $posId
     *
     * @return TransactionRegister
     */
    public function setPosId(int $posId)
    {
        $this->posId = $posId;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRegulationAccept(): ?bool
    {
        return $this->regulationAccept;
    }

    /**
     * @param bool $regulationAccept
     *
     * @return TransactionRegister
     */
    public function setRegulationAccept(bool $regulationAccept)
    {
        $this->regulationAccept = $regulationAccept;

        return $this;
    }

    public function getSdkVersion(): ?string
    {
        return $this->sdkVersion;
    }

    /**
     * @param string $sdkVersion
     *
     * @return TransactionRegister
     */
    public function setSdkVersion(string $sdkVersion)
    {
        $this->sdkVersion = $sdkVersion;

        return $this;
    }

    public function getSessionId(): ?Session
    {
        return $this->sessionId;
    }

    /**
     * @param Session $sessionId
     *
     * @return TransactionRegister
     */
    public function setSessionId(Session $sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getShipping(): ?int
    {
        return $this->shipping;
    }

    /**
     * @param int $shipping
     *
     * @return TransactionRegister
     */
    public function setShipping(int $shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getSign(): ?string
    {
        return $this->sign;
    }

    /**
     * @param string $sign
     *
     * @return TransactionRegister
     */
    public function setSign(string $sign)
    {
        $this->sign = $sign;

        return $this;
    }

    public function calculateSign($crc)
    {
        $data = [
            'sessionId' => $this->sessionId,
            'merchantId' => $this->merchantId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'crc' => $crc,
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);
        $this->sign = $sign;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    /**
     * @param int $timeLimit
     *
     * @return TransactionRegister
     */
    public function setTimeLimit(int $timeLimit)
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    public function getTransferLabel(): ?string
    {
        return $this->transferLabel;
    }

    /**
     * @param string $transferLabel
     *
     * @return TransactionRegister
     */
    public function setTransferLabel(string $transferLabel)
    {
        $this->transferLabel = $transferLabel;

        return $this;
    }

    public function getUrlReturn(): ?string
    {
        return $this->urlReturn;
    }

    /**
     * @param string $urlReturn
     *
     * @return TransactionRegister
     */
    public function setUrlReturn(string $urlReturn)
    {
        $this->urlReturn = $urlReturn;

        return $this;
    }

    public function getUrlCardPaymentNotification(): ?string
    {
        return $this->urlCardPaymentNotification;
    }

    /**
     * @return TransactionRegister
     */
    public function setUrlCardPaymentNotification(string $urlCardPaymentNotification)
    {
        $this->urlCardPaymentNotification = $urlCardPaymentNotification;

        return $this;
    }

    public function getUrlStatus(): ?string
    {
        return $this->urlStatus;
    }

    /**
     * @param string $urlStatus
     *
     * @return TransactionRegister
     */
    public function setUrlStatus(string $urlStatus)
    {
        $this->urlStatus = $urlStatus;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWaitForResult(): ?bool
    {
        return $this->waitForResult;
    }

    /**
     * @param bool $waitForResult
     *
     * @return TransactionRegister
     */
    public function setWaitForResult(bool $waitForResult)
    {
        $this->waitForResult = $waitForResult;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     *
     * @return TransactionRegister
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;

        return $this;
    }

    public function getUrlNotify()
    {
        return $this->urlNotify;
    }

    /**
     * @param mixed $urlNotify
     *
     * @return TransactionRegister
     */
    public function setUrlNotify($urlNotify)
    {
        $this->urlNotify = $urlNotify;

        return $this;
    }

    public function getCardData(): ?CardData
    {
        return $this->cardData;
    }

    public function setCardData(?CardData $cardData): TransactionRegister
    {
        $this->cardData = $cardData;

        return $this;
    }
}
