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

namespace Przelewy24\Provider\Payload;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\TransactionRegister;
use Przelewy24\Calculator\AmountExtraChargeCalculator;
use Przelewy24\Configuration\Enum\OrderIdEnum;
use Przelewy24\Configuration\Enum\StatusDriverEnum;
use Przelewy24\Dto\SessionIdConfig;
use Przelewy24\Factory\Session\SessionIdFactory;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Translator\Adapter\Translator;

class TransactionRegisterPayloadProvider
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var AmountExtraChargeCalculator
     */
    private $chargeCalculator;
    /**
     * @var SessionIdFactory
     */
    private $sessionIdFactory;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(
        UrlHelper $urlHelper,
        AmountExtraChargeCalculator $chargeCalculator,
        SessionIdFactory $sessionIdFactory,
        Translator $translator
    ) {
        $this->urlHelper = $urlHelper;
        $this->chargeCalculator = $chargeCalculator;
        $this->sessionIdFactory = $sessionIdFactory;
        $this->translator = $translator;
    }

    public function buildPayload(int $id_payment, \Cart $cart, Przelewy24Config $config, bool $regulationsAccepted, SessionIdConfig $sessionIdConfig)
    {
        $customer = new \Customer($cart->id_customer);
        $address = new \Address($cart->id_address_invoice);
        $session = $this->sessionIdFactory->getSessionId($sessionIdConfig);

        $transactionRegister = new TransactionRegister();
        $transactionRegister->setMerchantId((int) $config->getCredentials()->getIdMerchant());
        $transactionRegister->setPosId((int) $config->getCredentials()->getShopId());
        $transactionRegister->setSessionId($session);
        $transactionRegister->setCurrency((string) $config->getAccount()->getIsoCurrency());
        $transactionRegister->setAmount(
            (int) round(($cart->getOrderTotal() + $this->chargeCalculator->getAmount($config->getExtraCharge(), $cart)) * 100));

        $idOrder = \Order::getIdByCartId($cart->id);
        $cartDescription = $this->translator->trans('cart', [], 'Modules.Przelewy24payment.Payload');
        $description = $cartDescription . ' ' . $cart->id;
        if ($idOrder) {
            $order = new \Order($idOrder);
            $identification = $config->getOrder()->getOrderIdentification() === OrderIdEnum::ID ? $order->id : $order->reference;
            $orderDescription = $this->translator->trans('order', [], 'Modules.Przelewy24payment.Payload');
            $description = $orderDescription . ' ' . $identification;
        }
        $transactionRegister->setDescription((string) $description);

        $transactionRegister->setEmail((string) $customer->email);
        $transactionRegister->setClient($customer->firstname . ' ' . $customer->lastname);
        $transactionRegister->setAddress($address->address1 . ' ' . $address->address2);
        $transactionRegister->setZip((string) $address->postcode);
        $transactionRegister->setCity((string) $address->city);
        $idCountry = $address->id_country ? $address->id_country : \Context::getContext()->country->id;
        $transactionRegister->setCountry(\Country::getIsoById((int) $idCountry));
        $transactionRegister->setLanguage(\Context::getContext()->language->iso_code);
        $transactionRegister->setMethod((int) $id_payment);
        $transactionRegister->setUrlReturn((string) $this->urlHelper->getUrlReturn($session->getHash()));
        $transactionRegister->setUrlStatus((string) $this->urlHelper->getUrlStatus(StatusDriverEnum::TRANSACTION_STATUS));
        $transactionRegister->setShipping((int) 0);
        $transactionRegister->setWaitForResult((bool) $config->getTime()->getWaitForResult());
        $transactionRegister->setTimeLimit((int) $config->getTime()->getTimeLimit());
        $transactionRegister->setRegulationAccept((bool) $regulationsAccepted);
        $transactionRegister->setEncoding('UTF-8');
        $transactionRegister->calculateSign($config->getCredentials()->getSalt());

        return $transactionRegister;
    }
}
