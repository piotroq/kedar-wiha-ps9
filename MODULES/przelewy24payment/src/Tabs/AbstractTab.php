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
use Przelewy24\Translator\Adapter\Translator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

abstract class AbstractTab implements TabInterface
{
    protected $collection = [];

    protected $active = 0;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var Translator
     */
    protected $translator;

    protected static $extraParams = [];

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    public static function addExtraParam($key, $value)
    {
        static::$extraParams[$key] = $value;
    }

    public function __construct(Environment $twig, Translator $translator)
    {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->optionsResolver = new OptionsResolver();
    }

    /**
     * @return CollectionTabs
     */
    public function getCollection(): CollectionTabs
    {
        return $this->collection;
    }

    /**
     * @param CollectionTabs $collection
     */
    public function setCollection(CollectionTabs $collection): void
    {
        $this->collection = $collection;
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function changeActive(bool $active)
    {
        $this->active = $active;
    }

    public function setActive(): void
    {
        /* @var AbstractTab $tab */
        foreach ($this->collection->getCollection() as $tab) {
            $tab->changeActive(false);
        }
        $this->active = true;
    }
}
