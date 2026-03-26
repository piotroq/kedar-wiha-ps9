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

namespace Przelewy24\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Przelewy24\Exceptions\FrontMessageException;
use Przelewy24\Factory\Form\RefundFormFactory;
use Przelewy24\Handler\Order\OrderRefundHandler;
use Przelewy24\Presenter\Order\Przelewy24OrderPresenter;
use Przelewy24\Transformer\FormDataToRefundOrderTransformer;
use Symfony\Component\HttpFoundation\Request;

class RefundController extends FrameworkBundleAdminController
{
    public function refundOrderAction(
        $id_order,
        RefundFormFactory $formFactory,
        Request $request,
        FormDataToRefundOrderTransformer $dataToRefundOrderTransformer,
        OrderRefundHandler $orderRefundHandler,
        Przelewy24OrderPresenter $przelewy24OrderPresenter
    ) {
        $error = '';
        $status = false;
        try {
            $form = $formFactory->factory($id_order);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $refundOrder = $dataToRefundOrderTransformer->transform($data);
                $orderRefundHandler->refund($refundOrder);
                $status = true;
            }
        } catch (FrontMessageException $fe) {
            $error = $fe->getFrontMessage();
            $status = false;
        } catch (\Exception $e) {
            $status = false;
            $error = $e->getMessage();
        }

        $renderer = $przelewy24OrderPresenter->present($id_order);
        $renderer->getHistoryPaymentRenderer()->render();

        return $this->json([
            'refund_form_content' => $renderer->getRefundFormRenderer()->render(),
            'history_payment_content' => $renderer->getHistoryPaymentRenderer()->render(),
            'success' => $status,
            'error' => $error,
        ]);
    }
}
