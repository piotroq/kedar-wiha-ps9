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

namespace DpdShipping\Controller\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Currency;
use DpdShipping\Config\Config;
use DpdShipping\Domain\Configuration\Carrier\Query\GetCodPaymentModulesHandler;
use DpdShipping\Domain\Configuration\Carrier\Query\GetOrderCarrier;
use DpdShipping\Domain\Configuration\Configuration\Query\GetConfiguration;
use DpdShipping\Domain\Configuration\Configuration\Repository\Configuration;
use DpdShipping\Domain\Configuration\Connection\Query\GetConnectionList;
use DpdShipping\Domain\Configuration\Payer\Query\GetPayerList;
use DpdShipping\Domain\Configuration\SenderAddress\Query\GetOrderSenderAddressList;
use DpdShipping\Domain\Order\Command\AddDpdOrderCommand;
use DpdShipping\Domain\Order\Query\GetEmpikOrderReference;
use DpdShipping\Domain\Order\Query\GetEmpikPickupNumber;
use DpdShipping\Domain\Order\Query\GetOrderPudoCode;
use DpdShipping\Domain\Order\Query\GetOrderSource;
use DpdShipping\Domain\Order\Query\GetOrderSourceHandler;
use DpdShipping\Domain\Order\Query\GetReceiverAddressList;
use Module;
use Order;
use PrestaShop\PrestaShop\Adapter\Validate;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

class DpdShippingGenerateShippingController extends FrameworkBundleAdminController
{
    private $textFormDataHandler;
    private $commandBus;
    private $twig;
    private $translator;

    public function __construct($textFormDataHandler, $commandBus, $twig, $translator)
    {
        $this->textFormDataHandler = $textFormDataHandler;
        $this->commandBus = $commandBus;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function index($params, Request $request = null)
    {
        $successMsg = '';
        $errors = [];

        if ($request == null) {
            $request = Request::createFromGlobals();
            $orderId = $params['id_order'];
        } else {
            $orderId = (int)$request->get('orderId');
        }

        $order = new Order($orderId);

        $dpdCarrier = $this->commandBus->handle(new GetOrderCarrier($order));

        $opts = $this->buildDpdFormOptions($order->id_shop);
        $dpdCarrierType = isset($dpdCarrier['dpd_carrier']) ? $dpdCarrier['dpd_carrier']->getType() : '';

        $orderSource = $this->commandBus->handle(new GetOrderSource($orderId, $order->id_shop));

        $textForm = $this->textFormDataHandler->getFormFor($orderId, [], [
            'api_connection_list' => $opts['api_connection_list'],
            'payer_number_list' => $opts['payer_number_list'],
            'default_connection_id' => $opts['default_connection_id'],
            'default_payer_by_conn' => $opts['default_payer_by_conn'],
            'dpd_carrier' => $dpdCarrierType,
            'is_dpd_carrier' => !empty($dpdCarrierType) && substr($dpdCarrierType, 0, 3) === "DPD",
            'dpdPudoFinderUrl' => Config::PICKUP_MAP_BASE_URL,
            'order_currency' => (new Currency($order->id_currency))->iso_code,
            'order_amount' => $order->total_paid_tax_incl,
            'order_pickup_number' => $this->getOrderPickupNumber($orderSource, $orderId, $order),
        ]);

        if ($request != null) {
            $textForm->handleRequest($request);
        }

        if ($textForm->isSubmitted() && $textForm->isValid()) {

            $idConnection = $textForm->getData()['connection_id'] ?? null;
            $generateShippingResult = $this->commandBus->handle(new AddDpdOrderCommand($orderId, $textForm->getData(), $dpdCarrier, $order->id_shop, $idConnection));

            if (empty($generateShippingResult['errors'])) {
                $successMsg = $this->translator->trans('The shipment has been generated: %waybills%', ['%waybills%' => $this->getWaybills($generateShippingResult)], 'Admin.Notifications.Success');

                return $this->twig->render('@Modules/dpdshipping/views/templates/admin/order/generate-shipping-success.html.twig', [
                    'form' => [],
                    'successMsg' => $successMsg,
                    'errorMsg' => '',
                    'isShippingGenerated' => true,
                    'showReturnLabel' => $this->isReturnLabel($textForm->getData()),
                    'orderId' => $orderId,
                    'shippingHistoryId' => $this->getShippingHistoryId($generateShippingResult),
                ]);
            } else {
                $errors = $generateShippingResult['errors'];
            }
        }

        $result = [
            'form' => $textForm->createView(),
            'successMsg' => $successMsg,
            'errors' => empty($errors) ? [] : $errors,
            'products' => $this->getProducts($order->getOrderDetailList()),
            'receiver_address_list' => $this->commandBus->handle(new GetReceiverAddressList($order, true)),
            'sender_address_list' => $this->commandBus->handle(new GetOrderSenderAddressList($order->id_shop)),
            'dpd_carrier' => $dpdCarrier['carrier'] ?? '',
            'is_dpd_carrier' => !empty($dpdCarrierType) && substr($dpdCarrierType, 0, 3) === "DPD",
            'dpdPudoFinderUrl' => Config::PICKUP_MAP_BASE_URL,
            'package_group_type' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PACKAGE_GROUPING_WAY, $order->id_shop)), 'single'),
            'is_cod_payment' => $this->isCodPayment($this->commandBus, $order),
            'payment_method' => $this->getPaymentMethodDisplayName($order->module),
            'order_source' => $orderSource,
            'content_source' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PARAM_CONTENT, $order->id_shop)), ''),
            'content_source_static' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PARAM_CONTENT_STATIC_VALUE, $order->id_shop)), ''),
            'customer_source' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PARAM_CUSTOMER_DATA, $order->id_shop)), ''),
            'customer_source_static' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PARAM_CUSTOMER_DATA_STATIC_VALUE, $order->id_shop)), ''),
            'order_reference' => $order->reference,
            'order_reference_empik' => $this->commandBus->handle(new GetEmpikOrderReference($order->id, $order->id_shop)),
            'order_id' => $orderId,
            'invoice_number' => $order->invoice_number,
            'default_weight' => $this->getValue($this->commandBus->handle(new GetConfiguration(Configuration::DEFAULT_PARAM_WEIGHT, $order->id_shop)), '0')
        ];

        return $this->twig->render('@Modules/dpdshipping/views/templates/admin/order/generate-shipping.html.twig', $result);
    }


    private function getWaybills($generateShippingResult)
    {
        $waybills = '';
        foreach ($generateShippingResult as $item) {
            $waybills .= implode(', ', $item['waybills']) . ', ';
        }

        return rtrim($waybills, ', ');
    }

    public static function isPickup($dpdCarrier)
    {
        if (!isset($dpdCarrier['dpd_carrier'])) {
            return false;
        }

        return $dpdCarrier['dpd_carrier']->getType() == Config::DPD_PICKUP
            || $dpdCarrier['dpd_carrier']->getType() == Config::DPD_PICKUP_COD
            || $dpdCarrier['dpd_carrier']->getType() == Config::DPD_SWIP_BOX;
    }

    public function isCodPayment($commandBus, Order $order): bool
    {
        $paymentMethodsCod = $commandBus->handle(new GetConfiguration(Configuration::DPD_COD_PAYMENT_METHODS, $order->id_shop));

        return GetCodPaymentModulesHandler::isCodPaymentMethod($paymentMethodsCod, $order->module);
    }

    private function getPaymentMethodDisplayName($module)
    {
        if ($module == null) {
            return '';
        }
        $module = Module::getInstanceByName($module);

        if (!Validate::isLoadedObject($module)) {
            return '';
        }

        return $module->displayName;
    }

    private function isReturnLabel($form): bool
    {
        return isset($form['service_return_label']) && $form['service_return_label'] == '1';
    }

    /**
     * @param $generateShippingResult
     * @return mixed
     */
    public function getShippingHistoryId($generateShippingResult)
    {
        if (is_array($generateShippingResult) && isset($generateShippingResult[0]['shippingHistoryList'][0])) {
            return $generateShippingResult[0]['shippingHistoryList'][0]->getId();
        }

        return null;
    }

    private function buildDpdFormOptions($idShop): array
    {
        $connectionConfig = $this->commandBus->handle(new GetConnectionList($idShop));
        $payerList = $this->commandBus->handle(new GetPayerList(true, $idShop, null));

        $apiConnectionList = [];
        $defaultConnectionId = null;

        foreach ($connectionConfig as $c) {
            $label = $c->getName() . ", MASTERFID: " . $c->getMasterfid();
            $apiConnectionList[$label] = $c->getId();

            if ($defaultConnectionId === null && $c->isDefault()) {
                $defaultConnectionId = $c->getId();
            }
        }
        if ($defaultConnectionId === null && !empty($connectionConfig)) {
            $defaultConnectionId = $connectionConfig[0]->getId();
        }

        $payerNumberList = [];
        $defaultPayerByConn = [];

        foreach ($payerList as $p) {
            $connId = (int)$p->getIdConnection();
            if (!isset($payerNumberList[$connId])) {
                $payerNumberList[$connId] = [];
            }

            $label = $p->getName() . ", FID: " . $p->getFid();
            $payerNumberList[$connId][$label] = $p->getFid();

            if (!isset($defaultPayerByConn[$connId]) && $p->isDefault()) {
                $defaultPayerByConn[$connId] = $p->getFid();
            }
        }

        foreach ($payerNumberList as $connId => $choices) {
            if (!isset($defaultPayerByConn[$connId])) {
                $firstId = reset($choices); // first value (payerId)
                if ($firstId !== false) {
                    $defaultPayerByConn[$connId] = $firstId;
                }
            }
        }

        return [
            'api_connection_list' => $apiConnectionList,
            'payer_number_list' => $payerNumberList,
            'default_connection_id' => $defaultConnectionId,
            'default_payer_by_conn' => $defaultPayerByConn,
        ];
    }

    /**
     * @param array $orderDetails
     * @return array
     */
    public function getProducts(array $orderDetails): array
    {
        $products = [];
        foreach ($orderDetails as $orderDetail) {
            $products[] = [
                'product_id' => $orderDetail['product_id'],
                'product_name' => $orderDetail['product_name'],
                'product_quantity' => $orderDetail['product_quantity'],
                'product_weight' => $orderDetail['product_weight'],
                'product_reference' => $orderDetail['product_reference'],
            ];
        }
        return $products;
    }

    public function getValue($contentSource, $defaultValue): string
    {
        return $contentSource != null ? $contentSource->getValue() : $defaultValue;
    }

    /**
     * @param $orderSource
     * @param int $orderId
     * @param Order $order
     * @return mixed
     */
    public function getOrderPickupNumber($orderSource, int $orderId, Order $order)
    {
        $dpdPickupNumber = $this->commandBus->handle(new GetOrderPudoCode($order->id_shop, $order->id_cart));
        if (empty($dpdPickupNumber) && in_array($orderSource, [GetOrderSourceHandler::DELIVERY_EMPIK_STORE, GetOrderSourceHandler::EMPIK])) {
            return $this->commandBus->handle(new GetEmpikPickupNumber($orderId, $order->id_shop));
        }
        return $dpdPickupNumber;
    }
}
