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

namespace Przelewy24\Helper\Db;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DbHelper
{
    public const UNIQUE = 'UNIQUE';

    public const INDEX = 'INDEX';

    public const INNODB = 'InnoDB';

    public const MYISAM = 'MyISAM';

    public const CASCADE = 'CASCADE';

    public const SET_NULL = 'SET NULL';

    public const NO_ACTION = 'NO ACTION';

    public const RESTRICT = 'RESTRICT';

    /**
     * @param $table
     * @param $column
     * @param $def
     *
     * @return bool
     */
    public function safeAddColumn($table, $column, $def)
    {
        $count = \Db::getInstance()->getValue('SELECT count(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME=\'' . $column . '\' AND TABLE_NAME=\'' . _DB_PREFIX_ . $table . '\'');
        if (!$count) {
            return \Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $def);
        }

        return true;
    }

    /**
     * @param $table
     * @param $column
     *
     * @return bool
     */
    public function safeDropColumn($table, $column)
    {
        $count = \Db::getInstance()->getValue('SELECT count(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME=\'' . $column . '\' AND TABLE_NAME=\'' . _DB_PREFIX_ . $table . '\'');
        if ($count) {
            return \Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` DROP `' . $column . '`');
        }

        return true;
    }

    public function alterTable($table, $column, $columnParams)
    {
        return \Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . $table . ' CHANGE `' . $column . '` `' . $column . '` ' . $columnParams
        );
    }

    /**
     * @param $table
     * @param $indexName
     * @param $type
     * @param $columns
     *
     * @return bool
     */
    public function safeAddIndex($table, $indexName, $type, $columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $result = \Db::getInstance()->executeS('SHOW INDEX FROM ' . _DB_PREFIX_ . $table . ' WHERE Key_name="' . $indexName . '"');
        if (!$result) {
            return \Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . $table . ' ADD ' . $type . ' ' . $indexName . ' (' . implode(',', $columns) . ')');
        }

        return true;
    }

    public function safeDropIndex($table, $indexName)
    {
        $result = \Db::getInstance()->executeS('SHOW INDEX FROM ' . _DB_PREFIX_ . $table . ' WHERE Key_name="' . $indexName . '"');
        if ($result) {
            return \Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . $table . ' DROP INDEX ' . $indexName);
        }

        return true;
    }

    /**
     * @param $table
     * @param $columns
     *
     * @return bool
     */
    public function safeAddPrimaryKey($table, $columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $result = \Db::getInstance()->executeS('SHOW INDEX FROM ' . _DB_PREFIX_ . $table . ' WHERE Key_name="PRIMARY"');
        if (!$result) {
            return \Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . $table . ' ADD PRIMARY KEY (' . implode(',', $columns) . ')');
        }

        return true;
    }

    /**
     * @param $table
     * @param $indexName
     * @param $type
     * @param $columns
     *
     * @return bool
     */
    public function safeDropPrimaryIndex($table)
    {
        $result = \Db::getInstance()->executeS('SHOW INDEX FROM ' . _DB_PREFIX_ . $table . ' WHERE Key_name="PRIMARY"');
        if ($result) {
            return \Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . $table . ' DROP PRIMARY KEY');
        }

        return true;
    }

    /**
     * @param $name
     * @param $table
     * @param $tableReferenced
     * @param $update
     * @param $delete
     * @param $columnName
     * @param string|null $columnNameReferenced
     *
     * @return bool
     */
    public function safeAddConstraintKey($name, $table, $tableReferenced, $update, $delete, $columnName, $columnNameReferenced = null)
    {
        if (!$this->foreignKeyExists($table, $name)) {
            $columnNameReferenced = empty($columnNameReferenced) ? $columnName : $columnNameReferenced;

            return \Db::getInstance()->execute('
                ALTER TABLE ' . _DB_PREFIX_ . pSQL($table) . '
                ADD CONSTRAINT ' . pSQL($name) . '
                    FOREIGN KEY (' . pSQL($columnName) . ')
                    REFERENCES ' . _DB_PREFIX_ . pSQL($tableReferenced) . '(' . pSQL($columnNameReferenced) . ')
                    ON DELETE ' . pSQL($delete) . '
                    ON UPDATE ' . pSQL($update)
            );
        }

        return true;
    }

    public function safeDropForeignKey($table, $name)
    {
        if ($this->foreignKeyExists($table, $name)) {
            return \Db::getInstance()->execute('
                ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '`
                DROP FOREIGN KEY ' . pSQL($name)
            );
        }

        return true;
    }

    protected function foreignKeyExists($table, $name)
    {
        $sql = 'SELECT *
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND CONSTRAINT_NAME LIKE "' . pSQL($name) . '"
        AND TABLE_NAME LIKE "' . pSQL(_DB_PREFIX_ . $table) . '"';

        return (bool) \Db::getInstance()->getValue('SELECT EXISTS (' . $sql . ')');
    }

    /**
     * @param $table
     * @param $columns
     * @param $primary
     * @param $engine
     *
     * @return bool
     */
    public function createTable($table, $columns, $primary, $engine, $charset = 'utf8', $collate = 'utf8_general_ci')
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $table . '(';
        foreach ($columns as $column => $values) {
            $sql .= '`' . $column . '` ' . $values . ' ,';
        }
        $sql .= 'PRIMARY KEY (' . implode(',', $primary) . ')) ';
        $sql .= 'ENGINE = ' . pSQL($engine);
        $sql .= ' CHARSET = ' . pSQL($charset);
        $sql .= ' COLLATE = ' . pSQL($collate);

        $result = \Db::getInstance()->execute($sql);

        if ($result) {
            foreach ($columns as $column => $values) {
                $result &= $this->safeAddColumn($table, $column, $values);
            }
        }

        return $result;
    }

    public function dropTable($table)
    {
        return \Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . $table . ';');
    }

    public function disableForeignKeys()
    {
        \Db::getInstance()->execute('SET FOREIGN_KEY_CHECKS = 0;');
    }

    public function enableForeignKeys()
    {
        \Db::getInstance()->execute('SET FOREIGN_KEY_CHECKS = 1;');
    }
}
