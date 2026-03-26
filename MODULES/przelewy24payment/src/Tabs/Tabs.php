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

namespace Przelewy24\Tabs;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Tabs\Interfaces\TabInterface;
use Twig\Environment;

class Tabs
{
    /**
     * @var CollectionTabs
     */
    public $collectionTabs;

    /**
     * @var Environment
     */
    public $twig;

    public $name;

    public function __construct(Environment $twig)
    {
        $this->collectionTabs = new CollectionTabs();
        $this->twig = $twig;
    }

    public function setTabs(CollectionTabs $collectionTabs)
    {
        $this->collectionTabs = $collectionTabs;
    }

    public function addTab(TabInterface $tab)
    {
        $this->collectionTabs->addTab($tab);
    }

    public function removeTab(TabInterface $tab)
    {
        $this->collectionTabs->removeTab($tab);
    }

    public function getTabs()
    {
        return $this->collectionTabs;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function render()
    {
        $this->collectionTabs->sortById();
        $this->collectionTabs->setActiveifNotSet();

        return $this->twig->render('@Modules/przelewy24payment/views/templates/admin/config/tabs.html.twig', ['tabs_core' => $this->collectionTabs, 'tabs_core_name' => $this->getName()]);
    }
}
