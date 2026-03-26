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

namespace Przelewy24\Migrator;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\OrderIdEnum;
use Przelewy24\Migrator\Dto\Account;
use Przelewy24\Migrator\Provider\OldConfigurationProvider;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Translator\Adapter\Translator;

class ConfigurationMigrator
{
    private $configurationProvider;

    private $logs = [];
    private $translator;

    public function __construct(
        OldConfigurationProvider $configurationProvider,
        Translator $translator
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->translator = $translator;
    }

    public function migrate()
    {
        $this->logs = [];
        $accounts = Przlewy24AccountModel::getAllAccounts();
        $oldConfiguration = $this->configurationProvider->getOldConfigurations();

        foreach ($accounts as $row) {
            $accountExists[$row['id_shop']][$row['id_account']] = $row['id_currency'];
        }

        foreach ($oldConfiguration as $account) {
            if (isset($accountExists[$account->getIdShop()]) && $idAccount = array_search($account->getIdCurrency(), $accountExists[$account->getIdShop()])) {
                $this->_addConfiguration($account, $idAccount);
            } else {
                $this->_addAccount($account);
            }
        }
        $this->_migrateCards();
    }

    public function getLogs()
    {
        return $this->logs;
    }

    private function _addAccount(Account $account)
    {
        \Db::getInstance()->insert(
            'przelewy24_account',
            [
                'id_shop' => (int) $account->getIdShop(),
                'id_currency' => (int) $account->getIdCurrency(),
                'test_mode' => (int) $account->isTestMode(),
            ],
            false,
            true,
            \Db::INSERT_IGNORE
        );
        $idAccount = \Db::getInstance()->Insert_ID();

        \Db::getInstance()->insert(
            'przelewy24_state_config',
            [
                'id_account' => (int) $idAccount,
                'id_state_before_payment' => \Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
                'id_state_after_payment' => \Configuration::get('PS_OS_PAYMENT'),
            ],
            false,
            true,
            \Db::INSERT_IGNORE
        );
        \Db::getInstance()->insert(
            'przelewy24_order_config',
            [
                'id_account' => (int) $idAccount,
                'order_identification' => OrderIdEnum::ID,
            ],
            false,
            true,
            \Db::INSERT_IGNORE
        );

        $this->_addConfiguration($account, $idAccount);
    }

    private function _addConfiguration($account, int $idAccount)
    {
        $accountLog = $this->translator->trans('Account (SHOP: %shop%, CURRENCY: %currency%, SANDBOX: %testMode%) ', ['%shop%' => $account->getShopName(), '%currency%' => $account->getIsoCode(), '%testMode%' => (int) $account->isTestMode()], 'Modules.Przelewy24payment.Migration');

        $result = \Db::getInstance()->insert(
            'przelewy24_account',
            [
                'id_account' => (int) $idAccount,
                'id_shop' => (int) $account->getIdShop(),
                'id_currency' => (int) $account->getIdCurrency(),
                'test_mode' => (int) $account->isTestMode(),
            ],
            false,
            true,
            \Db::ON_DUPLICATE_KEY
        );

        if ($account->getCredentials() !== null) {
            $result = \Db::getInstance()->insert(
                'przelewy24_credentials_config',
                [
                    'id_account' => (int) $idAccount,
                    'id_merchant' => pSQL($account->getCredentials()->getIdMerchant()),
                    'shop_id' => pSQL($account->getCredentials()->getIdMerchant()),
                    'salt' => pSQL($account->getCredentials()->getSalt()),
                    'api_key' => pSQL($account->getCredentials()->getApiKey()),
                    'test_mode' => (bool) $account->isTestMode(),
                ],
                false,
                true,
                \Db::ON_DUPLICATE_KEY
            );
            if ($result) {
                $this->logs[] = ['success' => $accountLog . $this->translator->trans('Migrated credentials', [], 'Modules.Przelewy24payment.Migration')];
            } else {
                $this->logs[] = ['error' => $accountLog . $this->translator->trans('Error while migrated credentials', [], 'Modules.Przelewy24payment.Migration')];
            }
        }
        if ($account->getExtraCharge() !== null) {
            $result = \Db::getInstance()->insert(
                'przelewy24_extra_charge_config',
                [
                    'id_account' => (int) $idAccount,
                    'extra_charge_amount' => (float) $account->getExtraCharge()->getExtraChargeAmount(),
                    'extra_charge_percent' => (int) $account->getExtraCharge()->getExtraChargePercent(),
                ],
                false,
                true,
                \Db::ON_DUPLICATE_KEY
            );
        }
        if ($result) {
            $this->logs[] = ['success' => $accountLog . $this->translator->trans('Migrated extra charge', [], 'Modules.Przelewy24payment.Migration')];
        } else {
            $this->logs[] = ['error' => $accountLog . $this->translator->trans('Error while migrated extra charge', [], 'Modules.Przelewy24payment.Migration')];
        }
    }

    private function _migrateCards()
    {
        $migrated = false;
        try {
            $sql = new \DbQuery();
            $sql->select('customer_id, reference_id, mask, expires, card_type');
            $sql->from('przelewy24_recuring');
            $oldCards = \Db::getInstance()->executeS($sql);

            $sql = new \DbQuery();
            $sql->select('`id_card`, `ref_id`, `mask`, `card_date`, `type`, `id_customer`, `default`');
            $sql->from('przelewy24_cards');
            $cards = \Db::getInstance()->executeS($sql);
            $existsCards = [];
            foreach ($cards as $card) {
                $existsCards[$card['id_customer']][] = $card['ref_id'];
            }

            foreach ($oldCards as $row) {
                if (!isset($existsCards[$row['customer_id']]) || !in_array($row['reference_id'], $existsCards[$row['customer_id']])) {
                    $migrated = true;
                    \Db::getInstance()->insert(
                        'przelewy24_cards',
                        [
                            'id_customer' => (int) $row['customer_id'],
                            'ref_id' => pSQL($row['reference_id']),
                            'mask' => pSQL($row['mask']),
                            'card_date' => pSQL($row['expires']),
                            'type' => pSQL($row['card_type']),
                        ],
                        false,
                        true,
                        \Db::INSERT_IGNORE
                    );
                }
            }
            if ($migrated) {
                $this->logs[] = ['success' => $this->translator->trans('Migrated cards', [], 'Modules.Przelewy24payment.Migration')];
            }
        } catch (\Exception $e) {
        }
    }
}
