<?php

namespace DpdShipping\Support;

use Module;

class ContainerHelper
{
    public static function getFromContainer(Module $module, string $serviceId)
    {
        if (method_exists($module, 'get')) {
            try {
                return $module->get($serviceId);
            } catch (\Throwable $e) {
            }
        }

        if (class_exists('\\PrestaShop\\PrestaShop\\Adapter\\SymfonyContainer')) {
            try {
                $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
                if ($container && $container->has($serviceId)) {
                    return $container->get($serviceId);
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    public static function getQueryBus(Module $module)
    {
        return self::getFromContainer($module, 'prestashop.core.query_bus');
    }

    public static function getCommandBus(Module $module)
    {
        return self::getFromContainer($module, 'prestashop.core.command_bus');
    }

    public static function getDatabaseConnectionForInstaller(Module $module)
    {
        $conn = self::getFromContainer($module, 'database_connection');
        if ($conn) {
            return $conn;
        }
        $conn = self::getFromContainer($module, 'doctrine.dbal.default_connection');
        if ($conn) {
            return $conn;
        }

        if (class_exists('\\Db')) {
            try {
                $db = \Db::getInstance();
                if ($db) {
                    return $db;
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            if (defined('_DB_SERVER_') && defined('_DB_NAME_') && defined('_DB_USER_')) {
                $charset = 'utf8';
                $dsn = 'mysql:host=' . _DB_SERVER_ . ';dbname=' . _DB_NAME_ . ';charset=' . $charset;
                $pdo = new \PDO($dsn, _DB_USER_, defined('_DB_PASSWD_') ? _DB_PASSWD_ : '', [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
                return $pdo;
            }
        } catch (\Throwable $e) {
        }

        return null;
    }
}
