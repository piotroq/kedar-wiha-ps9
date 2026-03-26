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

namespace Przelewy24\Helper\Url;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Connection\Connection;
use Przelewy24\Model\Przlewy24AccountModel;

class UrlHelper
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \Module
     */
    private $module;

    public function __construct(\Context $context, \Module $module)
    {
        $this->context = $context;
        $this->module = $module;
    }

    public function getUrlFrontPayment(int $idPayment, int $idCart)
    {
        return $this->context->link->getModuleLink($this->module->name, 'payment', ['id_payment' => $idPayment, 'id_cart' => $idCart]);
    }

    public function getUrlReturn($sessionHash)
    {
        return $this->context->link->getModuleLink($this->module->name, 'confirmation', ['session_hash' => $sessionHash]);
    }

    public function getUrlStatus($type)
    {
        return $this->context->link->getModuleLink($this->module->name, 'status', ['type' => $type]);
    }

    public function getUrlFrontImage(string $imgName): string
    {
        return $this->module->getPathUri() . 'views/img/' . $imgName;
    }

    public function getCheckUrl($sessionHash): string
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'confirmation',
            [
                'ajax' => true,
                'action' => 'checkStatus',
                'session_hash' => $sessionHash,
            ]
        );
    }

    public function getBlikAjaxCheckUrl($sessionHash): string
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'blik',
            [
                'ajax' => true,
                'action' => 'checkStatus',
                'session_hash' => $sessionHash,
            ]
        );
    }

    public function getValidateMerchantUrl(): string
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'apple',
            [
                'ajax' => true,
                'action' => 'validateMerchant',
            ]
        );
    }

    public function getRepayUrl($idCart): string
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            'repayment',
            [
                'token' => Przlewy24AccountModel::getTokenByIdCart($idCart),
            ]
        );
    }

    public function getUrlPanel($testMode, $orderId): string
    {
        $url = $testMode ? Connection::SANDBOX . Connection::PANEL_URL : Connection::PROD . Connection::PANEL_URL;

        return $url . '?id=' . $orderId;
    }

    public function getUrlCardsController(): string
    {
        return $this->context->link->getModuleLink($this->module->name, 'cards');
    }

    public function getUrlCardsControllerWithInformation(): string
    {
        return $this->context->link->getModuleLink($this->module->name, 'cards', ['show_info' => 1]);
    }

    public function getRegulationsUrl($iso)
    {
        return $iso == 'pl' ? 'https://www.przelewy24.pl/regulamin' : 'https://www.przelewy24.pl/en/regulations';
    }

    public function getInformationGdprUrl($iso)
    {
        return $iso == 'pl' ? 'https://www.przelewy24.pl/obowiazekinformacyjny' : 'https://www.przelewy24.pl/en/information-obligation-gdpr-payer';
    }
}
