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

namespace Przelewy24\Payment\Voter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Api\Przelewy24\Response\PrzelewyResposne;
use Przelewy24\Exceptions\ChargeByCodeException;
use Przelewy24\Translator\Adapter\Translator;

class BlikChargeByCodeResultVoter
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function voteResult(PrzelewyResposne $resposne)
    {
        if ($resposne->getStatus() !== 201) {
            throw new ChargeByCodeException('Error when registering blik code', $this->translator->trans('Error when registering blik code', [], 'Modules.Przelewy24payment.Exception'));
        }
        $data = $resposne->getData();
        if (!isset($data['orderId']) || empty($data['orderId'])) {
            throw new ChargeByCodeException('orderId is null', $this->translator->trans('orderId is null', [], 'Modules.Przelewy24payment.Exception'));
        }
    }
}
