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

namespace Przelewy24\Resolver\PaymentType;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\PaymentTypeEnum;
use Przelewy24\Dto\PaymentMethod;

class CardTypeResolver implements PaymentTypeResolverInterface
{
    private const NAME = ['Karta płatnicza'];
    private const GROUP = ['Credit Card'];
    private const SUBGROUP = ['Credit Card'];
    private $paymentMethod;

    public function resolve(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        if (
            $this->_voteName()
            && $this->_voteGroup()
            && $this->_voteSubgroup()
        ) {
            $paymentMethod->setType(PaymentTypeEnum::CARD_PAYMENT);
        }
    }

    private function _voteName()
    {
        return in_array($this->paymentMethod->getName(), self::NAME);
    }

    private function _voteGroup()
    {
        return in_array($this->paymentMethod->getGroup(), self::GROUP);
    }

    private function _voteSubgroup()
    {
        return in_array($this->paymentMethod->getSubgroup(), self::SUBGROUP);
    }
}
