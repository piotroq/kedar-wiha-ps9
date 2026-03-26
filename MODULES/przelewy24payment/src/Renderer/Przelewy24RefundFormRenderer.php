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

class Przelewy24RefundFormRenderer implements RendererInterface
{
    /**
     * @var object|Environment|null
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        //        $container = SymfonyContainer::getInstance();
        //        if ($container) {
        //            $this->twig = $container->get('twig');
        //        }
    }

    private $form;

    private $refundDetail;

    private $allowedRefund;

    private $allowRender = true;

    /**
     * @return mixed
     */
    public function getAllowedRefund()
    {
        return $this->allowedRefund;
    }

    /**
     * @param mixed $allowedRefund
     *
     * @return Przelewy24RefundFormRenderer
     */
    public function setAllowedRefund($allowedRefund)
    {
        $this->allowedRefund = $allowedRefund;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     *
     * @return Przelewy24RefundFormRenderer
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefundDetail()
    {
        return $this->refundDetail;
    }

    /**
     * @param mixed $refundDetail
     *
     * @return Przelewy24RefundFormRenderer
     */
    public function setRefundDetail($refundDetail)
    {
        $this->refundDetail = $refundDetail;

        return $this;
    }

    public function isAllowRender(): bool
    {
        return $this->allowRender;
    }

    public function setAllowRender(bool $allowRender): Przelewy24RefundFormRenderer
    {
        $this->allowRender = $allowRender;

        return $this;
    }

    public function render()
    {
        if ($this->isAllowRender()) {
            return $this->twig->render('@Modules/przelewy24payment/views/templates/admin/order/_partials/refund_order.html.twig', $this->getData());
        }

        return false;
    }

    public function getData()
    {
        return [
            'form' => $this->getForm(),
            'refundDetail' => $this->getRefundDetail(),
            'allowedRefund' => $this->getAllowedRefund(),
            'is_177' => \Tools::version_compare(_PS_VERSION_, '1.7.7', '>='),
        ];
    }
}
