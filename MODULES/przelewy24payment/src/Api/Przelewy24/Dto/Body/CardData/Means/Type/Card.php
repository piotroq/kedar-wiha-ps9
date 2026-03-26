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

class Card implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    /* @var ?string $pan */
    private $pan;

    /* @var ?int $expYear */
    private $expYear;

    /* @var ?int $expMont */
    private $expMont;

    /* @var ?string $clientName */
    private $clientName;

    /* @var ?string $securityCode */
    private $securityCode;

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): Card
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getExpMont(): ?int
    {
        return $this->expMont;
    }

    public function setExpMont(?int $expMont): Card
    {
        $this->expMont = $expMont;

        return $this;
    }

    public function getExpYear(): ?int
    {
        return $this->expYear;
    }

    public function setExpYear(?int $expYear): Card
    {
        $this->expYear = $expYear;

        return $this;
    }

    public function getPan(): ?string
    {
        return $this->pan;
    }

    public function setPan(?string $pan): Card
    {
        $this->pan = $pan;

        return $this;
    }

    public function getSecurityCode(): ?string
    {
        return $this->securityCode;
    }

    public function setSecurityCode(?string $securityCode): Card
    {
        $this->securityCode = $securityCode;

        return $this;
    }
}
