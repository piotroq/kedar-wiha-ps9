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

namespace Przelewy24\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Card
{
    /* @var ?int $idCard */
    private $idCard;

    /* @var ?int $idCustomer */
    private $idCustomer;

    /* @var ?string $refId */
    private $refId;

    /* @var ?string $mask */
    private $mask;

    /* @var ?int $cardDate */
    private $cardDate;

    /* @var ?string $type */
    private $type;

    /* @var ?string $logo */
    private $logo;

    /* @var ?bool $default */
    private $default;

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): Card
    {
        $this->logo = $logo;

        return $this;
    }

    public function getCardDate(): ?int
    {
        return $this->cardDate;
    }

    public function getFormatedCardData()
    {
        $cardDate = (string) $this->getCardDate();
        $month = substr($cardDate, 0, 2);
        $year = substr($cardDate, -4, 4);

        return $month . '/' . $year;
    }

    public function setCardDate(?int $cardDate): Card
    {
        $this->cardDate = $cardDate;

        return $this;
    }

    public function getDefault(): ?bool
    {
        return $this->default;
    }

    public function setDefault(?bool $default): Card
    {
        $this->default = $default;

        return $this;
    }

    public function getIdCard(): ?int
    {
        return $this->idCard;
    }

    public function setIdCard(?int $idCard): Card
    {
        $this->idCard = $idCard;

        return $this;
    }

    public function getIdCustomer(): ?int
    {
        return $this->idCustomer;
    }

    public function setIdCustomer(?int $idCustomer): Card
    {
        $this->idCustomer = $idCustomer;

        return $this;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): Card
    {
        $this->mask = $mask;

        return $this;
    }

    public function getRefId(): ?string
    {
        return $this->refId;
    }

    public function setRefId(?string $refId): Card
    {
        $this->refId = $refId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Card
    {
        $this->type = $type;

        return $this;
    }

    public function isValidDate()
    {
        $dateTime = new \DateTime();
        $cardDate = (string) $this->getCardDate();
        $month = substr($cardDate, 0, 2);
        $year = substr($cardDate, -4, 4);
        if ((int) $year > (int) $dateTime->format('Y')) {
            return true;
        }

        if ((int) $year < (int) $dateTime->format('Y')) {
            return false;
        }
        if ((int) $month < (int) $dateTime->format('m')) {
            return false;
        }

        return true;
    }
}
