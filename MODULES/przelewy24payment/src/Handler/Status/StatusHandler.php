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

namespace Przelewy24\Handler\Status;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Exceptions\WrongStatusDriverException;
use Przelewy24\Handler\Status\Driver\Interfaces\StatusDriverInterface;
use Przelewy24\Translator\Adapter\Translator;

class StatusHandler
{
    private $drivers;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct($drivers, Translator $translator)
    {
        $this->drivers = $drivers;
        $this->translator = $translator;
    }

    public function handleStatus($statusType)
    {
        $driver = $this->_getDriver($statusType);

        return $driver->handle();
    }

    private function _getDriver($statusType): StatusDriverInterface
    {
        /* @var StatusDriverInterface $driver */
        foreach ($this->drivers as $driver) {
            if ($driver->getType() === $statusType) {
                return $driver;
            }
        }
        throw new WrongStatusDriverException('Wrong status driver', $this->translator->trans('Wrong status driver', [], 'Modules.Przelewy24payment.Exception'));
    }
}
