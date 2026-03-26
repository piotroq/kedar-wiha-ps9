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

namespace Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;

class XPayPayload implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    public const APPLE_PAY = 'applepay';

    public const GOOGLE_PAY = 'googlepay';

    /* @var ?string $payload */
    private $payload;

    /* @var ?string $type */
    private $type;

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): XPayPayload
    {
        $this->payload = $payload;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): XPayPayload
    {
        $this->type = $type;

        return $this;
    }
}
