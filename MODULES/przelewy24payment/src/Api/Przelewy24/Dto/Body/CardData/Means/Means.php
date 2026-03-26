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

namespace Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\Card;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\ReferenceNumber;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\SchemaToken;
use Przelewy24\Api\Przelewy24\Dto\Body\CardData\Means\Type\XPayPayload;
use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;

class Means implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    /* @var Card $card */
    private $card;

    /* @var ReferenceNumber $referenceNumber */
    private $referenceNumber;

    /* @var SchemaToken $schemaToken */
    private $schemaToken;

    /* @var XPayPayload $xPayPayload */
    private $xPayPayload;

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): Means
    {
        $this->card = $card;

        return $this;
    }

    public function getReferenceNumber(): ?ReferenceNumber
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?ReferenceNumber $referenceNumber): Means
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getSchemaToken(): ?SchemaToken
    {
        return $this->schemaToken;
    }

    public function setSchemaToken(?SchemaToken $schemaToken): Means
    {
        $this->schemaToken = $schemaToken;

        return $this;
    }

    public function getXPayPayload(): ?XPayPayload
    {
        return $this->xPayPayload;
    }

    public function setXPayPayload(?XPayPayload $xPayPayload): Means
    {
        $this->xPayPayload = $xPayPayload;

        return $this;
    }
}
