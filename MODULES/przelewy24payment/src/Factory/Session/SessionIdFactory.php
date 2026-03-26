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

namespace Przelewy24\Factory\Session;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Dto\Session;
use Przelewy24\Dto\SessionIdConfig;

class SessionIdFactory
{
    /**
     * @var \Module
     */
    private $module;

    private const SEPARATOR = '_';

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    public function getVersionPrefix($addSeparator = true)
    {
        return 'presp24{' . _PS_VERSION_ . ':' . $this->module->version . '}' . ($addSeparator ? self::SEPARATOR : '');
    }

    public function getMethodPrefix(SessionIdConfig $sessionIdConfig, $addSeparator = true)
    {
        if ($sessionIdConfig->getIdMethod() === 0) {
            return 'pg' . ($addSeparator ? self::SEPARATOR : '');
        } else {
            return 'dirmet{' . $sessionIdConfig->getIdMethod() . '}' . ($addSeparator ? self::SEPARATOR : '');
        }
    }

    public function getSpecialMethodPrefix(SessionIdConfig $sessionIdConfig, $addSeparator = true)
    {
        if ($sessionIdConfig->getSpecialMethod() === null) {
            return '';
        }

        return $sessionIdConfig->getSpecialMethod() . ($addSeparator ? self::SEPARATOR : '');
    }

    public function getCoreSessionId(SessionIdConfig $sessionIdConfig)
    {
        $raw = $sessionIdConfig->getIdCart() . '|' . time() . '|' . bin2hex(random_bytes(8));
        $sessionID = $sessionIdConfig->getIdCart() . '|' . substr(sha1($raw), 0, 36);

        return $sessionID;
    }

    public function getSessionId(SessionIdConfig $sessionIdConfig): Session
    {
        $session =
            $this->getVersionPrefix()
            . $this->getMethodPrefix($sessionIdConfig)
            . $this->getSpecialMethodPrefix($sessionIdConfig)
            . $this->getCoreSessionId($sessionIdConfig);

        return new Session($session, $this->getHash($session));
    }

    private function getHash(string $session)
    {
        return md5($session . bin2hex(random_bytes(8)));
    }
}
