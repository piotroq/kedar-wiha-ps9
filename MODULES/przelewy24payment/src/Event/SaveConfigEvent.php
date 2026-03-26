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

namespace Przelewy24\Event;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Dto\Interfaces\DbInterface;
use Przelewy24\Model\Dto\Przelewy24Config;

class SaveConfigEvent extends Event
{
    public const NAME = 'przelewy24.save_config';

    private $data;
    private $config;

    public function __construct(DbInterface $data, Przelewy24Config $config)
    {
        $this->data = $data;
        $this->config = $config;
    }

    public function getData(): DbInterface
    {
        return $this->data;
    }

    public function setData(DbInterface $data): SaveConfigEvent
    {
        $this->data = $data;

        return $this;
    }

    public function getConfig(): Przelewy24Config
    {
        return $this->config;
    }

    public function setConfig(Przelewy24Config $oldConfig): SaveConfigEvent
    {
        $this->config = $oldConfig;

        return $this;
    }
}
