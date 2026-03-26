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

namespace Przelewy24\Manager\Cards;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Dto\CardNotify;
use Przelewy24\Factory\Transaction\TransactionScopeFactory;
use Przelewy24\Model\Przelewy24CardModel;

class CardsManager
{
    /**
     * @var TransactionScopeFactory
     */
    private $transactionScopeFactory;

    public function __construct(TransactionScopeFactory $transactionScopeFactory)
    {
        $this->transactionScopeFactory = $transactionScopeFactory;
    }

    public function addCardFromNotify(CardNotify $cardNotify)
    {
        $transactionScope = $this->transactionScopeFactory->factory($cardNotify->getSessionId());
        $result = $cardNotify->getResult();
        if ($result['error'] === '0' && isset($result['cardInfoData']) && $transactionScope->getTransaction()->getSaveCard() && $transactionScope->getCustomer()->is_guest == 0) {
            $cardData = $result['cardInfoData'];
            $model = Przelewy24CardModel::getCardByRefID($cardData['refId']);
            $model->id_customer = $transactionScope->getCustomer()->id;
            $model->ref_id = $cardData['refId'];
            $model->mask = $cardData['mask'];
            $model->card_date = $cardData['cardDate'];
            $model->type = $cardData['cardType'];

            return $model->save();
        }

        return false;
    }

    public function removeCard(string $refId)
    {
        $model = Przelewy24CardModel::getCardByRefID($refId);
        if (\Validate::isLoadedObject($model)) {
            $model->delete();
        }
    }

    public function markAsDefault(string $refId)
    {
        $model = Przelewy24CardModel::getCardByRefID($refId);
        if (\Validate::isLoadedObject($model)) {
            $model->markAsDefault();
        }
    }
}
