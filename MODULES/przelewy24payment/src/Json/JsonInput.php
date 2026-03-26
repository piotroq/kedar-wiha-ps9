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

namespace Przelewy24\Json;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JsonInput
{
    private $json;

    private $jsonDecoded;

    public function __construct()
    {
        $this->json = \Tools::file_get_contents('php://input');
        $this->jsonDecoded = json_decode($this->json, true);
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getDecodeJson()
    {
        return $this->jsonDecoded;
    }
}
