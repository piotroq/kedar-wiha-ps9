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

use Przelewy24\Collection\PaymentMethodCollection;
use Przelewy24\Configuration\Enum\OrderIdEnum;
use Przelewy24\Configuration\ModuleConfiguration;
use Przelewy24\Dto\BlikNotify;
use Przelewy24\Helper\Style\StyleHelper;
use Przelewy24\Model\Dto\Interfaces\DbInterface;
use Przelewy24\Model\Dto\Przelewy24BlikTransaction;
use Przelewy24\Model\Dto\Przelewy24Transaction;

class Przlewy24AccountModel extends \ObjectModel
{
    public $id_shop;

    public $id_currency;

    public $test_mode;

    private static $transactionInstances = [];

    public static $definition = [
        'table' => 'przelewy24_account',
        'primary' => 'id_account',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            /* Classic fields */
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => false],
            'id_currency' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => false],
            'test_mode' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'allow_null' => false],
        ],
    ];

    public function getTypeConfig($type)
    {
        $tableName = 'przelewy24_' . $type . '_config';
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from(pSQL($tableName));
        $sql->where('id_account = ' . (int) $this->id);

        return \Db::getInstance()->getRow($sql);
    }

    public function getCredentialsConfig()
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('przelewy24_credentials_config');
        $sql->where('id_account = ' . (int) $this->id);
        $sql->where('test_mode = ' . (int) $this->test_mode);

        return \Db::getInstance()->getRow($sql);
    }

    public function getPaymentMethodMain()
    {
        $sql = new \DbQuery();
        $sql->select('id_payment');
        $sql->from('przelewy24_payment_method_main');
        $sql->where('id_account = ' . (int) $this->id);
        $sql->orderBy('position ASC');
        $idsPayments = \Db::getInstance()->executeS($sql);

        return array_map('intval', array_column($idsPayments, 'id_payment'));
    }

    public function getPaymentMethodSeparate()
    {
        $sql = new \DbQuery();
        $sql->select('id_payment');
        $sql->from('przelewy24_payment_method_separate');
        $sql->where('id_account = ' . (int) $this->id);
        $sql->orderBy('position ASC');
        $idsPayments = \Db::getInstance()->executeS($sql);

        return array_map('intval', array_column($idsPayments, 'id_payment'));
    }

    public function getPaymentMethodNames($cache = true)
    {
        if (!$cache || !\Cache::isStored('przelewy24_' . $this->id . '_payment_method_name')) {
            $sql = new \DbQuery();
            $sql->select('*');
            $sql->from('przelewy24_payment_method_name');
            $sql->where('id_account = ' . (int) $this->id);
            $result = \Db::getInstance()->executeS($sql);
            $data = [];
            foreach ($result as $row) {
                $data[$row['id_payment']] = $row['name'];
            }
            \Cache::store('przelewy24_' . $this->id . '_payment_method_name', $data);
        }

        return \Cache::retrieve('przelewy24_' . $this->id . '_payment_method_name');
    }

    public function savePaymentMethodNames(PaymentMethodCollection $collection)
    {
        $collection = $collection->getOnlySpecialNameMethods();
        $specialNames = $this->getPaymentMethodNames();
        $stayIds = [];
        foreach ($collection as $key => $paymentMethod) {
            if (
                isset($specialNames[$paymentMethod->getId()])
                && $specialNames[$paymentMethod->getId()] === $paymentMethod->getSpecialName()) {
                $collection->unsetKey($key);
                $stayIds[] = $key;
            }
        }

        $stayIdsWhere = implode(',', array_map('intval', $stayIds));

        $where = 'id_account = ' . (int) $this->id;
        $where .= empty($stayIdsWhere) ? '' : ' AND id_payment NOT IN (' . $stayIdsWhere . ')';
        $result = \Db::getInstance()->delete('przelewy24_payment_method_name', $where);
        foreach ($collection as $paymentMethod) {
            $result &= \Db::getInstance()->insert(
                'przelewy24_payment_method_name',
                [
                    'id_account' => (int) $this->id,
                    'id_payment' => (int) $paymentMethod->getId(),
                    'name' => pSQL($paymentMethod->getSpecialName()),
                ]);
        }

        return $result;
    }

    public function savePaymentMethodSeparate(PaymentMethodCollection $collection)
    {
        $result = \Db::getInstance()->delete('przelewy24_payment_method_separate', 'id_account = ' . (int) $this->id);
        $i = 0;
        foreach ($collection as $item) {
            $result &= \Db::getInstance()->insert(
                'przelewy24_payment_method_separate',
                [
                    'id_account' => (int) $this->id,
                    'id_payment' => (int) $item->getId(),
                    'position' => (int) $i,
                ]);
            ++$i;
        }

        return $result;
    }

    public function savePaymentMethodMain(PaymentMethodCollection $collection)
    {
        $result = \Db::getInstance()->delete('przelewy24_payment_method_main', 'id_account = ' . (int) $this->id);
        $i = 0;
        foreach ($collection as $item) {
            $result &= \Db::getInstance()->insert(
                'przelewy24_payment_method_main',
                [
                    'id_account' => (int) $this->id,
                    'id_payment' => (int) $item->getId(),
                    'position' => (int) $i,
                ]);
            ++$i;
        }

        return $result;
    }

    public function getIsoCurrency()
    {
        $sql = new \DbQuery();
        $sql->select('c.iso_code');
        $sql->from('przelewy24_account', 'p24_a');
        $sql->innerJoin('currency', 'c', 'p24_a.id_currency = c.id_currency AND p24_a.id_account = ' . (int) $this->id);

        return \Db::getInstance()->getValue($sql);
    }

    public static function getAccountByIDCurrencyAndIdShop(int $id_currency, int $id_shop)
    {
        $sql = new \DbQuery();
        $sql->select('id_account');
        $sql->from('przelewy24_account');
        $sql->where('id_currency = ' . (int) $id_currency);
        $sql->where('id_shop = ' . (int) $id_shop);
        $idAccount = \Db::getInstance()->getValue($sql);
        if ($idAccount) {
            return new Przlewy24AccountModel((int) $idAccount);
        }

        return null;
    }

    public static function getFirstAccountByContext()
    {
        $id_shop = \Context::getContext()->shop->id;
        $id_currency = \Configuration::get('PS_CURRENCY_DEFAULT');
        $sql = new \DbQuery();
        $sql->select('id_account');
        $sql->from('przelewy24_account');
        $sql->where('id_shop = ' . (int) $id_shop);
        $sql->where('id_currency = ' . (int) $id_currency);
        $idAccount = \Db::getInstance()->getValue($sql);
        if ($idAccount) {
            return new Przlewy24AccountModel((int) $idAccount);
        }

        return null;
    }

    public static function getAllAccounts($idShop = null)
    {
        $sql = new \DbQuery();
        $sql->select('p24_a.*, c.iso_code');
        $sql->from('przelewy24_account', 'p24_a');
        $sql->innerJoin('currency', 'c', 'c.id_currency = p24_a.id_currency AND c.active = 1 AND c.deleted = 0');
        if ($idShop) {
            $sql->where('p24_a.id_shop = ' . (int) $idShop);
        }

        return \Db::getInstance()->executeS($sql);
    }

    public static function fillAccount()
    {
        $accounts = self::getAllAccounts();

        $accountExists = [];
        foreach ($accounts as $row) {
            $accountExists[$row['id_shop']][] = $row['id_currency'];
        }

        $shops = \Shop::getShops(true, null, true);
        $currencies = \Currency::findAll();
        foreach ($shops as $idShop) {
            foreach ($currencies as $currency) {
                if (isset($accountExists[$idShop]) && in_array($currency['id_currency'], $accountExists[$idShop])) {
                    continue;
                }
                \Db::getInstance()->insert('przelewy24_account', ['id_shop' => (int) $idShop, 'id_currency' => (int) $currency['id_currency']], false, true, \Db::INSERT_IGNORE);
                $idAccount = \Db::getInstance()->Insert_ID();
                \Db::getInstance()->insert('przelewy24_state_config', ['id_account' => (int) $idAccount, 'id_state_before_payment' => \Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'), 'id_state_after_payment' => \Configuration::get('PS_OS_PAYMENT')], false, true, \Db::INSERT_IGNORE);
                \Db::getInstance()->insert('przelewy24_order_config', ['id_account' => (int) $idAccount, 'order_identification' => OrderIdEnum::ID], false, true, \Db::INSERT_IGNORE);
                if ($currency['iso_code'] == 'PLN') {
                    \Db::getInstance()->insert('przelewy24_payment_method_separate', ['id_account' => (int) $idAccount, 'id_payment' => ModuleConfiguration::CALCULATOR_ID_PAYMENT, 'position' => 0], false, true, \Db::INSERT_IGNORE);
                }
            }
        }

        return true;
    }

    public static function addBlikTransaction(BlikNotify $notify)
    {
        $blikTransaction = new Przelewy24BlikTransaction();
        $blikTransaction->setOrderId($notify->getOrderId());
        $blikTransaction->setSessionId($notify->getSessionId());
        $blikTransaction->setMethod($notify->getMethod());
        $result = $notify->getResult();
        $blikTransaction->setError($result['error']);
        $blikTransaction->setMessage($result['message']);
        $blikTransaction->setStatus($result['status']);
        $blikTransaction->setTrxRef($result['trxRef']);

        return self::addTransaction($blikTransaction);
    }

    public static function addTransaction(DbInterface $transaction)
    {
        return \Db::getInstance()->insert($transaction->getTableName(), $transaction->getDatabaseFieldsArray(), true, true, \Db::ON_DUPLICATE_KEY);
    }

    public static function getBlikTransaction(string $sessionId)
    {
        $transaction = new Przelewy24BlikTransaction();

        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from($transaction->getTableName());
        $sql->where('session_id = "' . pSQL($sessionId) . '"');
        $result = \Db::getInstance()->getRow($sql);
        if (empty($result)) {
            return $transaction;
        }
        foreach ($result as $key => $value) {
            $seter = StyleHelper::seterForUnderscoreField($key);
            if (is_callable([$transaction, $seter])) {
                $transaction->{$seter}($value);
            }
        }

        return $transaction;
    }

    public static function getSessionByHash(string $hash)
    {
        $sql = new \DbQuery();
        $sql->select('session_id');
        $sql->from('przelewy24_transaction');
        $sql->where('session_hash = "' . pSQL($hash) . '"');

        return \Db::getInstance()->getValue($sql);
    }

    public static function getTransactionByHash(string $hash, $use_cache = true)
    {
        if ($use_cache && isset(self::$transactionInstances[$hash])) {
            return self::$transactionInstances[$hash];
        }
        $transaction = new Przelewy24Transaction();

        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from($transaction->getTableName());
        $sql->where('session_hash = "' . pSQL($hash) . '"');
        $result = \Db::getInstance()->getRow($sql);
        if (empty($result)) {
            return $transaction;
        }
        foreach ($result as $key => $value) {
            $seter = StyleHelper::seterForUnderscoreField($key);
            if (is_callable([$transaction, $seter])) {
                $transaction->{$seter}($value);
            }
        }
        self::$transactionInstances[$hash] = $transaction;

        return $transaction;
    }

    public static function getTransaction(string $sessionId, $use_cache = true)
    {
        if ($use_cache && isset(self::$transactionInstances[$sessionId])) {
            return self::$transactionInstances[$sessionId];
        }
        $transaction = new Przelewy24Transaction();

        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from($transaction->getTableName());
        $sql->where('session_id = "' . pSQL($sessionId) . '"');
        $result = \Db::getInstance()->getRow($sql);
        if (empty($result)) {
            return $transaction;
        }
        foreach ($result as $key => $value) {
            $seter = StyleHelper::seterForUnderscoreField($key);
            if (is_callable([$transaction, $seter])) {
                $transaction->{$seter}($value);
            }
        }
        self::$transactionInstances[$sessionId] = $transaction;

        return $transaction;
    }

    public static function getTransactionByIdCart($idCart)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('przelewy24_transaction');
        $sql->where('id_cart = ' . (int) $idCart);

        return \Db::getInstance()->executeS($sql);
    }

    public static function checkCartIsPayed($idCart)
    {
        $sql = new \DbQuery();
        $sql->select('count(*)');
        $sql->from('przelewy24_transaction');
        $sql->where('id_cart = ' . (int) $idCart);
        $sql->where('received is NOT NULL');

        return \Db::getInstance()->getValue($sql);
    }

    public static function addOrderPayed(int $idOrder)
    {
        return \Db::getInstance()->insert('przelewy24_order_payed', ['id_order' => (int) $idOrder, 'date_add' => pSQL(date('Y-m-d H:i:s'))], false, true, \Db::ON_DUPLICATE_KEY);
    }

    public static function addOrderCanceled(int $idOrder)
    {
        return \Db::getInstance()->insert('przelewy24_order_canceled', ['id_order' => (int) $idOrder, 'date_add' => pSQL(date('Y-m-d H:i:s'))], false, true, \Db::ON_DUPLICATE_KEY);
    }

    public static function checkOrderIsCanceled(int $idOrder)
    {
        $sql = new \DbQuery();
        $sql->select('id_order');
        $sql->from('przelewy24_order_canceled');
        $sql->where('id_order = ' . (int) $idOrder);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    public static function checkOrderIsPayed(int $idOrder)
    {
        $sql = new \DbQuery();
        $sql->select('id_order');
        $sql->from('przelewy24_order_payed');
        $sql->where('id_order = ' . (int) $idOrder);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    public static function getOrdersToCancel(int $hoursDiff, int $ieAccount, array $excludeIdPayment = [], array $specificIdPayment = [])
    {
        $sql = new \DbQuery();
        $sql->select('pt.ps_id_order');
        $sql->from('przelewy24_transaction', 'pt');
        $sql->leftJoin('przelewy24_order_payed', 'pop', 'pt.ps_id_order = pop.id_order');
        $sql->leftJoin('przelewy24_order_canceled', 'poc', 'pt.ps_id_order = poc.id_order');
        $sql->where('pop.id_order IS NULL');
        $sql->where('poc.id_order IS NULL');
        $sql->where('pt.received IS NULL');
        $sql->where('pt.ps_id_order IS NOT NULL');
        $sql->where('pt.id_account = ' . (int) $ieAccount);
        if (!empty($excludeIdPayment)) {
            $exclude = implode(',', array_map('intval', $excludeIdPayment));
            $sql->where('pt.id_payment NOT IN (' . $exclude . ')');
        }
        if (!empty($specificIdPayment)) {
            $specific = implode(',', array_map('intval', $specificIdPayment));
            $sql->where('pt.id_payment IN (' . $specific . ')');
        }

        $sql->groupBy('pt.id_cart');
        $sql->having('MAX(pt.date_add) < date_sub(now(),interval ' . (int) $hoursDiff . ' hour)');
        $result = \Db::getInstance()->executeS($sql);

        return array_column($result, 'ps_id_order');
    }

    public static function getAllTimeConfig()
    {
        $sql = new \DbQuery();
        $sql->select('id_account, time_limit_fast_transfer, time_limit_long_term');
        $sql->from('przelewy24_time_config');

        return \Db::getInstance()->executeS($sql);
    }

    public static function getSessionIdByOrderId($idOrder)
    {
        $sql = new \DbQuery();
        $sql->select('session_id');
        $sql->from('przelewy24_transaction');
        $sql->where('ps_id_order = ' . (int) $idOrder);
        $sql->where('received IS NOT NULL');

        return \Db::getInstance()->getValue($sql);
    }

    public static function getHistoryTransactionByIdOrder($idOrder)
    {
        $sql = new \DbQuery();
        $sql->select(
            '
            pt.p24_id_order,
            pt.ps_id_order,
            pt.session_id,
            pt.amount as payed_amount,
            pt.received,
            pt.test_mode,
            pt.date_add,
            pr.amount as refund_amount,
            pr.date_add as refund_date,
            pr.reference,
            pr.received as refund_received,
            pr.id_refund
                 ');
        $sql->from('przelewy24_transaction', 'pt');
        $sql->leftJoin('przelewy24_refund', 'pr', 'pt.session_id = pr.session_id');
        $sql->where('pt.ps_id_order = ' . (int) $idOrder);
        $sql->orderBy('pt.date_add');
        $result = \Db::getInstance()->executeS($sql);

        $data = [];
        foreach ($result as $row) {
            $data[$row['session_id']]['p24_id_order'] = $row['p24_id_order'];
            $data[$row['session_id']]['ps_id_order'] = $row['ps_id_order'];
            $data[$row['session_id']]['session_id'] = $row['session_id'];
            $data[$row['session_id']]['payed_amount'] = $row['payed_amount'];
            $data[$row['session_id']]['received'] = $row['received'];
            $data[$row['session_id']]['test_mode'] = $row['test_mode'];
            $data[$row['session_id']]['date_add'] = $row['date_add'];
            if (!empty($row['id_refund'])) {
                $data[$row['session_id']]['refunds'][$row['id_refund']]['refund_amount'] = $row['refund_amount'];
                $data[$row['session_id']]['refunds'][$row['id_refund']]['refund_date'] = $row['refund_date'];
                $data[$row['session_id']]['refunds'][$row['id_refund']]['reference'] = $row['reference'];
                $data[$row['session_id']]['refunds'][$row['id_refund']]['refund_received'] = $row['refund_received'];
            }
        }

        return $data;
    }

    public static function getTokenByIdCart($idCart)
    {
        $sql = new \DbQuery();
        $sql->select('token');
        $sql->from('przelewy24_repay_token');
        $sql->where('id_cart = ' . (int) $idCart);
        $token = \Db::getInstance()->getValue($sql);
        if (empty($token)) {
            $token = md5($idCart . uniqid('token_cart'));
            \Db::getInstance()->insert('przelewy24_repay_token', ['id_cart' => (int) $idCart, 'token' => pSQL($token)]);
        }

        return $token;
    }

    public static function getIdCartByToken($token)
    {
        $sql = new \DbQuery();
        $sql->select('id_cart');
        $sql->from('przelewy24_repay_token');
        $sql->where('token LIKE "' . pSQL($token) . '"');

        return \Db::getInstance()->getValue($sql);
    }

    public static function checkCartWasPayedByPrzelewy($idCart)
    {
        $sql = new \DbQuery();
        $sql->select('id_cart');
        $sql->from('przelewy24_transaction');
        $sql->where('id_cart = ' . (int) $idCart);

        return (bool) \Db::getInstance()->getValue($sql);
    }

    public static function getLastTransactionByCart($idCart)
    {
        $sql = new \DbQuery();
        $sql->select('id_cart, id_payment, date_add');
        $sql->from('przelewy24_transaction');
        $sql->where('id_cart = ' . (int) $idCart);
        $sql->orderBy('date_add DESC');

        return \Db::getInstance()->getRow($sql);
    }
}
