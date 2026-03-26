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

namespace Przelewy24\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Przelewy24RefundModel extends \ObjectModel
{
    public $session_id;

    public $amount;

    public $reference;

    public $description;

    public $date_add;

    public $received;

    public static $definition = [
        'table' => 'przelewy24_refund',
        'primary' => 'id_refund',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            /* Classic fields */
            'session_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'amount' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => false],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => false],
            'received' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        $this->reference = md5(mt_rand(1, 1000000) . uniqid((string) mt_rand(), true));

        return parent::add($auto_date, $null_values);
    }

    public static function getAllowedRefundAmount($session_id)
    {
        $sql = new \DbQuery();
        $sql->select('SUM(amount)');
        $sql->from('przelewy24_refund');
        $sql->where('session_id LIKE "' . pSQL($session_id) . '"');
        $sql->where('received IS NOT NULL');
        $count = \Db::getInstance()->getValue($sql);

        $sql = new \DbQuery();
        $sql->select('amount');
        $sql->from('przelewy24_transaction');
        $sql->where('session_id LIKE "' . pSQL($session_id) . '"');
        $amount = \Db::getInstance()->getValue($sql);

        return (int) $amount - (int) $count;
    }

    public static function isTransactionFullRefund($session_id)
    {
        $sql = new \DbQuery();
        $sql->select('SUM(amount)');
        $sql->from('przelewy24_refund');
        $sql->where('session_id LIKE "' . pSQL($session_id) . '"');
        $sql->where('received IS NOT NULL');
        $fullAmount = \Db::getInstance()->getValue($sql);

        $sql = new \DbQuery();
        $sql->select('session_id');
        $sql->from('przelewy24_transaction');
        $sql->where('session_id LIKE "' . pSQL($session_id) . '"');
        $sql->where('amount = ' . (int) $fullAmount);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    public static function getRefundDetailsBySessionId($session_id)
    {
        $sql = new \DbQuery();
        $sql->select('pr.*');
        $sql->from('przelewy24_refund', 'pr');
        $sql->where('session_id LIKE "' . pSQL($session_id) . '"');

        return \Db::getInstance()->executeS($sql);
    }

    public static function getModelByReference($reference)
    {
        $sql = new \DbQuery();
        $sql->select('id_refund');
        $sql->from('przelewy24_refund');
        $sql->where('reference LIKE "' . pSQL($reference) . '"');
        $idRefund = \Db::getInstance()->getValue($sql);

        return new Przelewy24RefundModel((int) $idRefund);
    }
}
