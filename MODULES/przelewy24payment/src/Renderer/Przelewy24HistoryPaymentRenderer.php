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

use Przelewy24\Collection\TransactionHistoryCollection;
use Przelewy24\Renderer\Interfaces\RendererInterface;
use Twig\Environment;

class Przelewy24HistoryPaymentRenderer implements RendererInterface
{
    private $transactionHistoryCollection;

    private $repayLink;

    /**
     * @var object|Environment|null
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->transactionHistoryCollection = new TransactionHistoryCollection();
    }

    public function getTransactionHistoryCollection(): TransactionHistoryCollection
    {
        return $this->transactionHistoryCollection;
    }

    /**
     * @return Przelewy24HistoryPaymentRenderer
     */
    public function setTransactionHistoryCollection(TransactionHistoryCollection $transactionHistoryCollection)
    {
        $this->transactionHistoryCollection = $transactionHistoryCollection;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRepayLink()
    {
        return $this->repayLink;
    }

    /**
     * @param mixed $repayLink
     *
     * @return Przelewy24HistoryPaymentRenderer
     */
    public function setRepayLink($repayLink)
    {
        $this->repayLink = $repayLink;

        return $this;
    }

    public function render()
    {
        return $this->twig->render('@Modules/przelewy24payment/views/templates/admin/order/_partials/payment_detail.html.twig', $this->getData());
    }

    public function getData()
    {
        return [
            'transactionHistoryCollection' => $this->getTransactionHistoryCollection(),
            'repayLink' => $this->getRepayLink(),
            'is_177' => \Tools::version_compare(_PS_VERSION_, '1.7.7', '>='),
        ];
    }
}
