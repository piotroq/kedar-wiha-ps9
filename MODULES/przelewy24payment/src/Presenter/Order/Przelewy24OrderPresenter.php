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

namespace Przelewy24\Presenter\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Factory\Form\RefundFormFactory;
use Przelewy24\Helper\Price\PriceHelper;
use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Model\Przelewy24RefundModel;
use Przelewy24\Model\Przlewy24AccountModel;
use Przelewy24\Renderer\Przelewy24OrderRenderer;
use Przelewy24\Transformer\ModelToTransactionHistoryCollectionTransformer;

class Przelewy24OrderPresenter
{
    /**
     * @var object|RefundFormFactory|null
     */
    private $refundFormFactory;

    /**
     * @var mixed
     */
    private $idOrder;

    /**
     * @var Przelewy24OrderRenderer
     */
    private $orderRenderer;

    /**
     * @var ModelToTransactionHistoryCollectionTransformer
     */
    private $collectionTransformer;

    /**
     * @var UrlHelper
     */
    private $urlHelper;
    private $priceHelper;

    public function __construct(
        Przelewy24OrderRenderer $orderRenderer,
        ModelToTransactionHistoryCollectionTransformer $collectionTransformer,
        RefundFormFactory $refundFormFactory,
        UrlHelper $urlHelper,
        PriceHelper $priceHelper
    ) {
        $this->refundFormFactory = $refundFormFactory;
        $this->orderRenderer = $orderRenderer;
        $this->collectionTransformer = $collectionTransformer;
        $this->urlHelper = $urlHelper;
        $this->priceHelper = $priceHelper;
    }

    public function present($idOrder)
    {
        $this->idOrder = $idOrder;

        $this->_presentRefundFormView();
        $this->_presentHistoryPaymentView();

        return $this->orderRenderer;
    }

    private function _presentRefundFormView()
    {
        $refundFormRenderer = $this->orderRenderer->getRefundFormRenderer();
        $sessionId = Przlewy24AccountModel::getSessionIdByOrderId($this->idOrder);
        if (!$sessionId) {
            $refundFormRenderer->setAllowRender(false);

            return;
        }
        $refundFormRenderer->setForm($this->refundFormFactory->factory($this->idOrder)->createView());
        $refundFormRenderer->setRefundDetail(Przelewy24RefundModel::getRefundDetailsBySessionId($sessionId));
        $refundFormRenderer->setAllowedRefund($this->priceHelper->displayPrice(Przelewy24RefundModel::getAllowedRefundAmount($sessionId) / 100));
    }

    private function _presentHistoryPaymentView()
    {
        $historyPaymentRenderer = $this->orderRenderer->getHistoryPaymentRenderer();
        $data = Przlewy24AccountModel::getHistoryTransactionByIdOrder($this->idOrder);
        $historyTransactionCollection = $this->collectionTransformer->transform($data);
        $historyPaymentRenderer->setTransactionHistoryCollection($historyTransactionCollection);
        $historyPaymentRenderer->setRepayLink($this->urlHelper->getRepayUrl(\Order::getCartIdStatic($this->idOrder)));
    }
}
