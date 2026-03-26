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

namespace Przelewy24\Event;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Helper\Price\PriceHelper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints as Assert;

class RefundFormEvent
{
    public function setData(FormEvent $event)
    {
        $priceHelper = new PriceHelper(\Context::getContext());

        $data = $event->getData();
        $form = $event->getForm();
        $order = new \Order($data['id_order']);
        $currency = new \Currency($order->id_currency);

        $productsOrder = $order->getProductsDetail();
        foreach ($productsOrder as $product) {
            $form->add(
                'order_detail_' . $product['id_order_detail'], IntegerType::class, [
                    'label' => $product['product_name'],
                    'attr' => [
                        'data-price-formated' => $priceHelper->displayPrice($product['unit_price_tax_incl'], $currency),
                        'data-price' => \Tools::ps_round($product['unit_price_tax_incl'], 2),
                        'class' => 'form-control--sm, p24_refund_row_product',
                        'min' => 0,
                        'max' => $product['product_quantity'] - $product['product_quantity_refunded'],
                    ],
                ]
            );
            $data['order_detail_' . $product['id_order_detail']] = 0;
        }
        $form->add(
            'extra_refund', MoneyType::class, [
                'label' => 'Extra refund',
                'required' => false,
                'scale' => 2,
                'currency' => $currency->iso_code,
                'constraints' => [
                    new Assert\Type(['type' => 'float']),
                ],
                'attr' => [
                    'class' => 'form-control--sm, p24_refund_total',
                    'min' => 0,
                ],
            ]
        );
        $form->add(
            'total_refund', MoneyType::class, [
                'label' => 'Total',
                'scale' => 2,
                'currency' => $currency->iso_code,
                'constraints' => [
                    new Assert\Type(['type' => 'float']),
                ],
                'attr' => [
                    'class' => 'form-control--sm, p24_refund_total',
                    'min' => 0,
                    'max' => 11,
                ],
            ]
        );

        $form->add('save', SubmitType::class, [
            'attr' => ['class' => 'btn-primary']]);
        $event->setData($data);
    }
}
