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

namespace Przelewy24\Api\Przelewy24\Dto\Body;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Dto\Body\Interfaces\PrzelewyBodyInterface;
use Przelewy24\Api\Przelewy24\Dto\Body\Traits\JsonSerializeTrait;

class TransactionRefund implements PrzelewyBodyInterface
{
    use JsonSerializeTrait;

    private $requestId;

    private $refunds = [];

    private $refundsUuid;

    private $urlStatus;

    public function getRefunds(): array
    {
        return $this->refunds;
    }

    public function setRefunds(array $refunds): TransactionRefund
    {
        $this->refunds = $refunds;

        return $this;
    }

    public function getRefundsUuid(): ?string
    {
        return $this->refundsUuid;
    }

    /**
     * @param string $refundsUuid
     *
     * @return TransactionRefund
     */
    public function setRefundsUuid(string $refundsUuid)
    {
        $this->refundsUuid = $refundsUuid;

        return $this;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     *
     * @return TransactionRefund
     */
    public function setRequestId(string $requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getUrlStatus(): ?string
    {
        return $this->urlStatus;
    }

    /**
     * @param string $urlStatus
     *
     * @return TransactionRefund
     */
    public function setUrlStatus(string $urlStatus)
    {
        $this->urlStatus = $urlStatus;

        return $this;
    }
}
