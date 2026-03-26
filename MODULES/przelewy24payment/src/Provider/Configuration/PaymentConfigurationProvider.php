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

namespace Przelewy24\Provider\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Factory\PaymentMethod\PaymentMethodCollectionFactory;
use Przelewy24\Model\Dto\PaymentConfig;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Provider\Configuration\Interfaces\ConfigurationProviderInterface;

class PaymentConfigurationProvider extends AbstractConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var PaymentMethodCollectionFactory
     */
    private $paymentMethodCollectionFactory;

    public function __construct(PaymentMethodCollectionFactory $paymentMethodCollectionFactory)
    {
        $this->paymentMethodCollectionFactory = $paymentMethodCollectionFactory;
    }

    protected function getType(): string
    {
        return 'payment';
    }

    protected function getObject(): object
    {
        return new PaymentConfig();
    }

    public function getConfiguration(Przlewy24AccountModel $model, $fillPayments = true, $excludePayments = false)
    {
        $paymentsToExclude = $excludePayments ? ModuleConfiguration::EXCLUDED_PYMENT : [];

        $paymentConfig = parent::getConfiguration($model);
        if ($fillPayments) {
            $paymentMethodsCollection = $this->paymentMethodCollectionFactory->factory(
                [
                    'account' => $model,
                    'currency' => $model->getIsoCurrency(),
                    'exclude_id' => $paymentsToExclude,
                ]
            );
            $paymentConfig->setPaymentMethodNameList($paymentMethodsCollection);

            $paymentMethodsMain = $model->getPaymentMethodMain();
            $paymentMethodsMain = empty($paymentMethodsMain) ? [0] : $paymentMethodsMain;
            $paymentConfig->setPaymentMethodInMainList($paymentMethodsCollection->intersectByIds($paymentMethodsMain));

            $paymentMethodsSeparate = $model->getPaymentMethodSeparate();
            $paymentMethodsSeparate = empty($paymentMethodsSeparate) ? [0] : $paymentMethodsSeparate;
            $paymentConfig->setPaymentMethodSeparateList($paymentMethodsCollection->intersectByIds($paymentMethodsSeparate));
        }

        return $paymentConfig;
    }
}
