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

namespace Przelewy24\Handler\Status\Driver;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\StatusDriverEnum;
use Przelewy24\Dto\BlikNotify;
use Przelewy24\Handler\Status\Driver\Interfaces\StatusDriverInterface;
use Przelewy24\Handler\Transaction\BlikTransactionHandler;
use Przelewy24\Helper\Style\StyleHelper;

class BlikStatusHandler extends JsonStatusDriverAbstract implements StatusDriverInterface
{
    private $blikTransactionHandler;

    public function __construct(BlikTransactionHandler $blikTransactionHandler)
    {
        $this->blikTransactionHandler = $blikTransactionHandler;
    }

    public function getType()
    {
        return StatusDriverEnum::BLIK_STATUS;
    }

    public function handle()
    {
        $this->getContent();
        $blikNotify = StyleHelper::fillObject(new BlikNotify(), $this->content);

        return $this->blikTransactionHandler->handle($blikNotify);
    }
}
