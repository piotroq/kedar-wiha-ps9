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

namespace Przelewy24\Api\Przelewy24\Dto\Body\CardData;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Means;
use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;

class CardData implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    public const TRANSACTION_STANDARD = 'standard';

    public const TRANSACTION_INITIAL = 'initial';

    public const TRANSACTION_1CLICK = '1click';

    public const TRANSACTION_RECURRING = 'recurring';

    /* @var ?Means $means */
    private $means;

    /* @var ?string $transactionType */
    private $transactionType;

    public function getMeans(): ?Means
    {
        return $this->means;
    }

    public function setMeans(?Means $means): CardData
    {
        $this->means = $means;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(?string $transactionType): CardData
    {
        $this->transactionType = $transactionType;

        return $this;
    }
}
