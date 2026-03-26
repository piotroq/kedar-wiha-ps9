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

use Przelewy24\Iterators\CollectionKeyIterator;
use Przelewy24\Tabs\Interfaces\TabInterface;

class CollectionTabs extends CollectionKeyIterator
{
    public function addTab(TabInterface $tab)
    {
        $this->collection[$tab->getId()] = $tab;
        $tab->setCollection($this);
    }

    public function removeTab(TabInterface $tab)
    {
        $this->unsetKey($tab->getId());
    }

    public function sortById()
    {
        ksort($this->collection);
    }

    public function setActiveifNotSet()
    {
        $isActive = false;
        /* @var TabInterface $tab */
        foreach ($this->collection as $tab) {
            if ($tab->isActive()) {
                $isActive = true;
                break;
            }
        }
        if (!$isActive) {
            $this->rewind();
            $this->current()->changeActive(true);
        }
    }
}
