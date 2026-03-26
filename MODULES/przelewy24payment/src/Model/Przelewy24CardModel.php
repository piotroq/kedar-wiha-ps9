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

use Przelewy24\Collection\CardsCollection;
use Przelewy24\Dto\Card;
use Przelewy24\Helper\Style\StyleHelper;

class Przelewy24CardModel extends \ObjectModel
{
    public $id_customer;

    public $ref_id;

    public $mask;

    public $card_date;

    public $type;

    public $default;

    public static $definition = [
        'table' => 'przelewy24_cards',
        'primary' => 'id_card',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            /* Classic fields */
            'ref_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => false],
            'mask' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'card_date' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'type' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => false],
            'default' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'allow_null' => false],
        ],
    ];

    public static function getCardByRefID($refId)
    {
        $sql = new \DbQuery();
        $sql->select('id_card');
        $sql->from('przelewy24_cards');
        $sql->where('ref_id LIKE "' . pSQL($refId) . '"');
        $idCard = \Db::getInstance()->getValue($sql);

        return new Przelewy24CardModel((int) $idCard);
    }

    public function markAsDefault()
    {
        \Db::getInstance()->update('przelewy24_cards', ['default' => 0], 'id_customer = ' . (int) $this->id_customer);
        $this->default = 1;

        return $this->save();
    }

    public static function getCardsByIdCustomer(int $idCustomer)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('przelewy24_cards');
        $sql->where('id_customer = ' . (int) $idCustomer);
        $result = \Db::getInstance()->executeS($sql);

        $collection = new CardsCollection();
        foreach ($result as $row) {
            $card = new Card();
            foreach ($row as $key => $value) {
                $seter = StyleHelper::seterForUnderscoreField($key);
                if (is_callable([$card, $seter])) {
                    $card->{$seter}($value);
                }
            }
            $collection->add($card);
        }

        return $collection;
    }

    public function delete()
    {
        $idCustomer = $this->id_customer;
        $default = $this->default;
        $result = parent::delete();
        if ($default) {
            $result &= self::markFirstCardAsDefault($idCustomer);
        }

        return $result;
    }

    public function add($auto_date = true, $null_values = false)
    {
        $result = parent::add($auto_date, $null_values);
        $result &= self::markFirstCardAsDefault($this->id_customer);

        return (bool) $result;
    }

    public static function markFirstCardAsDefault($idCustomer)
    {
        $cards = self::getCardsByIdCustomer($idCustomer);
        if (!$cards->hasDefaultCard() && !$cards->empty()) {
            $card = $cards->current();

            return \Db::getInstance()->update('przelewy24_cards', ['default' => 1], 'ref_id LIKE "' . pSQL($card->getRefId()) . '"');
        }

        return true;
    }
}
