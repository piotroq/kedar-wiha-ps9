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

namespace Przelewy24\Form\Transformer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Dto\PaymentMethod;
use Symfony\Component\Form\DataTransformerInterface;

class PaymentMethodTransformer implements DataTransformerInterface
{
    public function __construct()
    {
    }

    public function transform($value)
    {
        if ($value instanceof PaymentMethodCollection && $value->count()) {
            return implode(',', $value->keys());
        }

        return '';
    }

    public function reverseTransform($value)
    {
        $collection = new PaymentMethodCollection();
        if (empty($value)) {
            return $collection;
        }
        $idsPayment = explode(',', $value);

        foreach ($idsPayment as $idPayment) {
            $paymentMethod = new PaymentMethod();
            $paymentMethod->setId($idPayment);
            $collection->add($paymentMethod);
        }

        return $collection;
    }
}
