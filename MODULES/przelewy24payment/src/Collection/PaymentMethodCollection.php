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

namespace Przelewy24\Collection;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Dto\PaymentMethod;
use Przelewy24\Iterators\CollectionKeyIterator;

class PaymentMethodCollection extends CollectionKeyIterator
{
    public function add(PaymentMethod $paymentMethod)
    {
        $this->collection[$paymentMethod->getId()] = $paymentMethod;
    }

    public function sortPosition($ids)
    {
        uasort($this->collection, function ($a, $b) use ($ids) {
            $positionA = array_search($a->getId(), $ids);
            $positionB = array_search($b->getId(), $ids);
            $positionA = $positionA !== null ? $positionA : 9999;
            $positionB = $positionB !== null ? $positionB : 9999;

            if ($positionA == $positionB) {
                return 0;
            }

            return ($positionA < $positionB) ? -1 : 1;
        });
    }

    public function diffCollection(PaymentMethodCollection $collection)
    {
        $newPaymentCollection = new PaymentMethodCollection();
        foreach ($this->collection as $idPayment => $paymentMethod) {
            if ($collection->isset($idPayment)) {
                continue;
            }
            $newPaymentCollection->add($paymentMethod);
        }

        return $newPaymentCollection;
    }

    public function intersectByIds(array $ids)
    {
        $newPaymentCollection = new PaymentMethodCollection();
        foreach ($ids as $id) {
            if ($this->isset($id)) {
                $newPaymentCollection->add($this->getKey($id));
            }
        }

        return $newPaymentCollection;
    }

    public function removeIdPayment($id)
    {
        unset($this->collection[$id]);
    }

    public function removeTypePayment($type)
    {
        foreach ($this->collection as $idPayment => $paymentMethod) {
            if ($paymentMethod->getType() == $type) {
                unset($this->collection[$idPayment]);
            }
        }
    }

    public function getOnlySpecialNameMethods()
    {
        $newPaymentCollection = new PaymentMethodCollection();
        foreach ($this->collection as $idPayment => $paymentMethod) {
            if ($paymentMethod->getName() === $paymentMethod->getSpecialName()) {
                continue;
            }
            $newPaymentCollection->add($paymentMethod);
        }

        return $newPaymentCollection;
    }

    public function getPaymentMethodByType($type)
    {
        foreach ($this->collection as $paymentMethod) {
            if ($paymentMethod->getType() == $type) {
                return $paymentMethod;
            }
        }

        return null;
    }
}
