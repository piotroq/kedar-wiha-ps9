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

namespace Przelewy24\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Helper\Url\UrlHelper;
use Przelewy24\Hook\Interfaces\HookInterface;
use Przelewy24\Model\Przlewy24AccountModel;

class DisplayOrderDetail implements HookInterface
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(\Context $context, UrlHelper $urlHelper)
    {
        $this->context = $context;
        $this->urlHelper = $urlHelper;
    }

    public function execute($params)
    {
        $idCart = \Order::getCartIdStatic($params['order']->id);
        if (
            !Przlewy24AccountModel::checkCartWasPayedByPrzelewy($idCart)
            || Przlewy24AccountModel::checkOrderIsPayed($params['order']->id)
            || Przlewy24AccountModel::checkOrderIsCanceled($params['order']->id)
        ) {
            return false;
        }

        $this->context->smarty->assign(['link_repay' => $this->urlHelper->getRepayUrl($idCart)]);

        return $this->context->smarty->fetch('module:przelewy24payment/views/templates/hook/order_detail.tpl');
    }
}
