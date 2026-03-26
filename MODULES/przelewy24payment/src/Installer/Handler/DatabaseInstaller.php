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

namespace Przelewy24\Installer\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Helper\Db\DbHelper;
use Przelewy24\Installer\Handler\Interfaces\InstallerInterface;
use Przelewy24\Installer\Handler\Interfaces\UnInstallerInterface;
use Przelewy24\Parser\Yaml\YamlParser;

class DatabaseInstaller implements InstallerInterface, UnInstallerInterface
{
    private $parsedYaml = [];

    private $databaseData = [];

    private $dbHelper;

    public function install(ModuleConfiguration $configuration): bool
    {
        $yamlParser = new YamlParser();
        $this->parsedYaml = $yamlParser->parseYml(ModuleConfiguration::DATABASE_YML_FILE);
        $this->dbHelper = new DbHelper();

        return $this->_createDatabase();
    }

    public function uninstall(ModuleConfiguration $configuration): bool
    {
        $yamlParser = new YamlParser();
        $this->parsedYaml = $yamlParser->parseYml(ModuleConfiguration::DATABASE_YML_FILE);
        if (empty($this->parsedYaml)) {
            return true;
        }
        $this->dbHelper = new DbHelper();

        return $this->_dropDatabase();
    }

    protected function _createDatabase()
    {
        $this->databaseData = $this->parsedYaml['database'];
        $result = true;
        $result &= $this->createDatabase();
        $result &= $this->addDatabase();
        $result &= $this->dropDatabase();
        $result &= $this->alterDatabase();

        return $result;
    }

    protected function _dropDatabase()
    {
        $this->databaseData = $this->parsedYaml['database'];

        if (!isset($this->databaseData['create']['tables'])) {
            return true;
        }
        $return = true;
        $this->dbHelper->disableForeignKeys();
        foreach ($this->databaseData['create']['tables'] as $table) {
            $return &= $this->dbHelper->dropTable($table['table']);
        }
        $this->dbHelper->enableForeignKeys();

        return $return;
    }

    private function createDatabase()
    {
        if (!isset($this->databaseData['create']['tables'])) {
            return true;
        }
        $return = true;
        foreach ($this->databaseData['create']['tables'] as $table) {
            $charset = $table['charset'] ?? 'utf8';
            $collate = $table['collate'] ?? 'utf8_general_ci';
            $return &= $this->dbHelper->createTable(
                $table['table'],
                $table['columns'],
                $table['primary'],
                $table['engine'],
                $charset,
                $collate
            );
        }

        return $return;
    }

    private function addDatabase()
    {
        if (!isset($this->databaseData['add'])) {
            return true;
        }
        $return = true;

        $return &= $this->addColumnsDatabase($this->databaseData['add']);
        $return &= $this->addIndexesDatabase($this->databaseData['add']);
        $return &= $this->addConstraintKeysDatabase($this->databaseData['add']);
        $return &= $this->addPrimaryKeysDatabase($this->databaseData['add']);

        return $return;
    }

    private function addColumnsDatabase($add)
    {
        if (!isset($add['columns'])) {
            return true;
        }

        $return = true;
        foreach ($add['columns'] as $column) {
            $return &= $this->dbHelper->safeAddColumn($column['table'], $column['column'], $column['definition']);
        }

        return $return;
    }

    private function addIndexesDatabase($add)
    {
        if (!isset($add['indexes'])) {
            return true;
        }

        $return = true;
        foreach ($add['indexes'] as $index) {
            $return &= $this->dbHelper->safeAddIndex($index['table'], $index['index_name'], $index['type'], $index['columns']);
        }

        return $return;
    }

    private function addConstraintKeysDatabase($add)
    {
        if (!isset($add['constraint_keys'])) {
            return true;
        }

        $return = true;
        foreach ($add['constraint_keys'] as $key) {
            $columnNameRefernced = $key['column_name_referenced'] ?? $key['column_name'];
            $return &= $this->dbHelper->safeAddConstraintKey(
                $key['name'],
                $key['table'],
                $key['table_referenced'],
                $key['update'],
                $key['delete'],
                $key['column_name'],
                $columnNameRefernced
            );
        }

        return $return;
    }

    private function addPrimaryKeysDatabase($add)
    {
        if (!isset($add['primary_keys'])) {
            return true;
        }

        $return = true;
        foreach ($add['primary_keys'] as $primary) {
            $return &= $this->dbHelper->safeAddPrimaryKey(
                $primary['table'],
                $primary['columns']
            );
        }

        return $return;
    }

    private function dropDatabase()
    {
        if (!isset($this->databaseData['drop'])) {
            return true;
        }
        $return = true;

        $return &= $this->dropColumnsDatabase($this->databaseData['drop']);
        $return &= $this->dropIndexesDatabase($this->databaseData['drop']);
        $return &= $this->dropPrimaryKeysDatabase($this->databaseData['drop']);

        return $return;
    }

    private function dropColumnsDatabase($drop)
    {
        if (!isset($drop['columns'])) {
            return true;
        }

        $return = true;
        foreach ($drop['columns'] as $column) {
            $return &= $this->dbHelper->safeDropColumn($column['table'], $column['column']);
        }

        return $return;
    }

    private function dropIndexesDatabase($drop)
    {
        if (!isset($drop['indexes'])) {
            return true;
        }

        $return = true;
        foreach ($drop['indexes'] as $index) {
            $return &= $this->dbHelper->safeDropIndex($index['table'], $index['index_name']);
        }

        return $return;
    }

    private function dropPrimaryKeysDatabase($drop)
    {
        if (!isset($drop['primary_keys'])) {
            return true;
        }

        $return = true;
        foreach ($drop['primary_keys'] as $primary) {
            $return &= $this->dbHelper->safeDropPrimaryIndex(
                $primary['table']
            );
        }

        return $return;
    }

    private function alterDatabase()
    {
        if (!isset($this->databaseData['alter']['tables'])) {
            return true;
        }

        $return = true;
        foreach ($this->databaseData['alter']['tables'] as $table) {
            $return &= $this->dbHelper->alterTable(
                $table['table'],
                $table['column'],
                $table['column_params']
            );
        }

        return $return;
    }
}
