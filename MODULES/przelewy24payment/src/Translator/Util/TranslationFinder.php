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

namespace Przelewy24\Translator\Util;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Exception\FileNotFoundException;
use PrestaShop\PrestaShop\Core\Translation\Exception\TranslationFilesNotFoundException;
use PrestaShop\PrestaShop\Core\Translation\Storage\Finder\TranslationFinder as CoreTranslationFinder;
use PrestaShopBundle\Translation\Provider\TranslationFinder as LegacyTranslationFinder;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationFinder
{
    private $finder;

    public function __construct()
    {
        $this->finder = self::createNativeFinder();
    }

    public function getCatalogue(string $directory, string $locale): ?MessageCatalogue
    {
        $directory = sprintf('%s/%s', rtrim($directory), $locale);

        if (!is_dir($directory)) {
            return null;
        }

        try {
            return $this->finder->getCatalogueFromPaths([$directory], $locale);
        } catch (TranslationFilesNotFoundException|FileNotFoundException $e) {
            return null;
        }
    }

    /**
     * @return CoreTranslationFinder|LegacyTranslationFinder
     */
    private static function createNativeFinder()
    {
        if (class_exists(CoreTranslationFinder::class)) {
            return new CoreTranslationFinder();
        }

        if (class_exists(LegacyTranslationFinder::class)) {
            return new LegacyTranslationFinder();
        }

        throw new \RuntimeException('Unable to create translation finder: base class not found.');
    }
}
