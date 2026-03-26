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

namespace Przelewy24\Translator\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Translator\Interfaces\TranslatorInterface;

class Translator implements TranslatorInterface
{
    private $translator;

    public function __construct(\Context $context)
    {
        $this->translator = $context->getTranslator();
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    public function setLocale($locale)
    {
        return $this->translator->setLocale($locale);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $parameters
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    /**
     * Forwards calls to the inner translator.
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->translator->$name(...$arguments);
    }
}
