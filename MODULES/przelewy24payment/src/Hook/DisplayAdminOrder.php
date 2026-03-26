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

use Przelewy24\Hook\Interfaces\HookInterface;
use Przelewy24\Presenter\Order\Przelewy24OrderPresenter;

class DisplayAdminOrder implements HookInterface
{
    /**
     * @var Przelewy24OrderPresenter
     */
    private $przelewy24OrderPresenter;

    public function __construct(Przelewy24OrderPresenter $przelewy24OrderPresenter)
    {
        $this->przelewy24OrderPresenter = $przelewy24OrderPresenter;
    }

    public function execute($params)
    {
        $renderer = $this->przelewy24OrderPresenter->present($params['id_order']);

        return $renderer->render();
    }
}
