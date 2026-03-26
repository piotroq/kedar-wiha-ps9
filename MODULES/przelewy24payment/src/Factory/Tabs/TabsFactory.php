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

namespace Przelewy24\Factory\Tabs;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Model\Dto\Przelewy24Config;
use Przelewy24\Tabs\AbstractTab;
use Przelewy24\Tabs\CollectionTabs;
use Przelewy24\Tabs\Tabs;

class TabsFactory
{
    private $tabs;

    private $tabsCollection;

    private $tabsObject;

    public function __construct($tabs, Tabs $tabsObject)
    {
        $this->tabs = $tabs;
        $this->tabsCollection = new CollectionTabs();
        $this->tabsObject = $tabsObject;
    }

    public function factory(Przelewy24Config $config)
    {
        foreach ($this->tabs as $tab) {
            $this->tabsCollection->addTab($tab);
        }
        $this->tabsObject->setTabs($this->tabsCollection);
        $this->tabsObject->setName('config');
        AbstractTab::addExtraParam('config', $config);

        return $this->tabsObject;
    }
}
