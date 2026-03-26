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

namespace Przelewy24\Renderer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Przelewy24\Renderer\Interfaces\RendererInterface;
use Twig\Environment;

class Przelewy24OrderRenderer implements RendererInterface
{
    /**
     * @var Przelewy24RefundFormRenderer
     */
    private $refundFormRenderer;

    /**
     * @var Przelewy24HistoryPaymentRenderer
     */
    private $historyPaymentRenderer;

    /**
     * @var object|Environment|null
     */
    private $twig;

    public function __construct(Przelewy24RefundFormRenderer $refundFormRenderer, Przelewy24HistoryPaymentRenderer $historyPaymentRenderer, Environment $twig)
    {
        $this->refundFormRenderer = $refundFormRenderer;
        $this->historyPaymentRenderer = $historyPaymentRenderer;
        $this->twig = $twig;
        //        $container = SymfonyContainer::getInstance();
        //        if ($container) {
        //            $this->twig = $container->get('twig');
        //        }
    }

    public function getRefundFormRenderer(): Przelewy24RefundFormRenderer
    {
        return $this->refundFormRenderer;
    }

    public function setRefundFormRenderer(Przelewy24RefundFormRenderer $refundFormRenderer): Przelewy24OrderRenderer
    {
        $this->refundFormRenderer = $refundFormRenderer;

        return $this;
    }

    public function getHistoryPaymentRenderer(): Przelewy24HistoryPaymentRenderer
    {
        return $this->historyPaymentRenderer;
    }

    public function setHistoryPaymentRenderer(Przelewy24HistoryPaymentRenderer $historyPaymentRenderer): Przelewy24OrderRenderer
    {
        $this->historyPaymentRenderer = $historyPaymentRenderer;

        return $this;
    }

    public function render()
    {
        return $this->twig->render('@Modules/przelewy24payment/views/templates/admin/order/przelewy24_order.html.twig', $this->getData());
    }

    public function getData()
    {
        return [
            'historyPaymentRenderer' => $this->getHistoryPaymentRenderer(),
            'refundFormRenderer' => $this->getRefundFormRenderer(),
        ];
    }
}
