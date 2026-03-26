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

namespace Przelewy24\Factory\PaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Factory\ConnectionFactory;
use Przelewy24\Api\Przelewy24\Factory\PaymentRequestFactory;
use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Helper\Style\StyleHelper;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Processor\Payment\PaymentTypeProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodCollectionFactory
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var PaymentRequestFactory
     */
    private $paymentRequestFactory;

    private $options;
    private $paymentTypeProcessor;

    public function __construct(
        ConnectionFactory $connectionFactory,
        PaymentRequestFactory $paymentRequestFactory,
        PaymentTypeProcessor $paymentTypeProcessor
    ) {
        $this->optionsResolver = new OptionsResolver();
        $this->_configureOptions();
        $this->connectionFactory = $connectionFactory;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->paymentTypeProcessor = $paymentTypeProcessor;
    }

    public function factory($options = [])
    {
        try {
            $this->options = $this->optionsResolver->resolve($options);
            $response = $this->_getPaymentMethodsFromAPI();
            if ($response->getStatus() !== 200) {
                throw new \Exception('Payment method not found');
            }

            return $this->_createPaymentCollection($response->getData());
        } catch (\Exception $exception) {
        }

        return new PaymentMethodCollection();
    }

    private function _getPaymentMethodsFromAPI()
    {
        $connection = $this->connectionFactory->factory($this->options['account']);
        $paymentRequest = $this->paymentRequestFactory->factory($this->options['lang'], $this->options['amount'], $this->options['currency']);

        return $connection->sendRequest($paymentRequest);
    }

    private function _createPaymentCollection($data)
    {
        $data = $this->_filterPaymentMethods($data);
        $paymentCollection = new PaymentMethodCollection();
        foreach ($data as $row) {
            $paymentCollection->add($this->_createPaymentObject($row));
        }
        $this->paymentTypeProcessor->process($paymentCollection);
        if (!empty($this->options['filter_id'])) {
            $paymentCollection->sortPosition($this->options['filter_id']);
        }

        return $paymentCollection;
    }

    private function _filterPaymentMethods($payments)
    {
        foreach ($payments as $key => $value) {
            if (!isset($value['id'])) {
                unset($payments[$key]);
                continue;
            }
            if (!empty($this->options['filter_id']) && !in_array($value['id'], $this->options['filter_id'])) {
                unset($payments[$key]);
                continue;
            }
            if (in_array($value['id'], $this->options['exclude_id'])) {
                unset($payments[$key]);
                continue;
            }
        }

        return $payments;
    }

    private function _createPaymentObject($paymentData)
    {
        $specialNames = $this->options['account']->getPaymentMethodNames();
        $paymentMethod = new PaymentMethod();
        foreach ($paymentData as $key => $value) {
            if ($key == 'id' && isset($specialNames[$value])) {
                $paymentMethod->setSpecialName($specialNames[$value]);
            }

            $seter = StyleHelper::seterForUnderscoreField($key);
            if (is_callable([$paymentMethod, $seter])) {
                $paymentMethod->{$seter}($value);
            }
        }
        $paymentMethod->setType(PaymentTypeEnum::DEFAULT_PAYMENT);

        return $paymentMethod;
    }

    private function _configureOptions()
    {
        $this->optionsResolver->setDefined(['amount', 'currency', 'filter_id']);
        $this->optionsResolver->setDefaults(
            [
                'lang' => 'pl',
                'amount' => null,
                'currency' => 'PLN',
                'filter_id' => [],
                'exclude_id' => [],
            ]
        );
        $this->optionsResolver->setRequired(['account']);
        $this->optionsResolver->setAllowedValues('lang', ['pl', 'en']);
        $this->optionsResolver->setAllowedTypes('filter_id', 'int[]');
        $this->optionsResolver->setAllowedTypes('exclude_id', 'int[]');
        $this->optionsResolver->setAllowedTypes('account', Przlewy24AccountModel::class);
    }
}
