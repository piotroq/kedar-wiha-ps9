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
class SessionIdConfig
{
    private $idMethod;
    private $idCart;
    private $specialMethod;

    public function __construct(int $idMethod, int $idCart, string $specialMethod = null)
    {
        $this->idMethod = $idMethod;
        $this->idCart = $idCart;
        $this->specialMethod = $specialMethod;
    }

    public function getIdMethod(): int
    {
        return $this->idMethod;
    }

    public function getIdCart(): int
    {
        return $this->idCart;
    }

    public function getSpecialMethod(): ?string
    {
        return $this->specialMethod;
    }
}
