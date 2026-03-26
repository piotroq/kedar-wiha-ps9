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

namespace Przelewy24\Tabs\Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Configuration\Enum\FormTypeEnum;
use Przelewy24\Factory\Form\FormTabFactory;
use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Tabs\AbstractTab;
use Przelewy24\Tabs\Interfaces\TabInterface;
use Przelewy24\Translator\Adapter\Translator;
use Twig\Environment;

class OrderTab extends AbstractTab implements TabInterface
{
    /**
     * @var FormTabFactory
     */
    private $formTabFactory;

    /**
     * @var FormTypeEnum
     */
    private $formTypeEnum;

    public function __construct(
        Environment $twig,
        Translator $translator,
        FormTabFactory $formTabFactory,
        FormTypeEnum $formTypeEnum
    ) {
        parent::__construct($twig, $translator);
        $this->formTabFactory = $formTabFactory;
        $this->formTypeEnum = $formTypeEnum;
    }

    public function getId()
    {
        return 3;
    }

    public function getName()
    {
        return $this->translator->trans('Order', [], 'Modules.Przelewy24payment.Tab');
    }

    public function render()
    {
        $this->_configureOptions();
        $options = $this->optionsResolver->resolve(self::$extraParams);
        $form = $this->formTabFactory->factory($options['config'], FormTypeEnum::ORDER);
        $title = $this->formTypeEnum->getTabName(FormTypeEnum::ORDER);

        return $this->twig->render('@Modules/przelewy24payment/views/templates/admin/config/tabs/order.html.twig', ['form' => $form->createView(), 'title' => $title]);
    }

    private function _configureOptions()
    {
        $this->optionsResolver->setRequired('config');
        $this->optionsResolver->setAllowedTypes('config', Przelewy24Config::class);
    }
}
