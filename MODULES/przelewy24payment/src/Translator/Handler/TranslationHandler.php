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

namespace Przelewy24\Translator\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Translation;
use Przelewy24\Translator\Util\TranslationFinder;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationHandler
{
    private $entityManager;
    private $loader;
    private $finder;

    public function __construct()
    {
        $this->entityManager = SymfonyContainer::getInstance()->get('doctrine.orm.entity_manager');
        $this->loader = SymfonyContainer::getInstance()->get('prestashop.translation.database_loader');
        $this->finder = new TranslationFinder();
    }

    public function handle($directory): void
    {
        $languages = $this->entityManager->getRepository(Lang::class)->findAll();

        foreach ($languages as $language) {
            $this->importTranslations($directory, $language);
        }
    }

    private function importTranslations(string $directory, Lang $language): void
    {
        $catalogue = $this->finder->getCatalogue($directory, $language->getLocale());

        if (null === $catalogue) {
            return;
        }

        $catalogue = $this->filterCatalogue($catalogue);

        foreach ($catalogue->all() as $domain => $messages) {
            foreach ($messages as $id => $translation) {
                $translation = $this->createTranslation($id, $translation, $domain, $language);
                $this->entityManager->persist($translation);
            }
        }

        $this->entityManager->flush();
    }

    private function filterCatalogue(MessageCatalogue $fileCatalogue): MessageCatalogue
    {
        $messages = $fileCatalogue->all();
        $locale = $fileCatalogue->getLocale();

        foreach ($fileCatalogue->getDomains() as $domain) {
            $databaseCatalogue = $this->loader->load(null, $locale, $domain)->all($domain);
            $messages[$domain] = array_diff_key($messages[$domain], $databaseCatalogue);

            if ([] === $messages[$domain]) {
                unset($messages[$domain]);
            }
        }

        return new MessageCatalogue($locale, $messages);
    }

    private function createTranslation(string $id, string $translation, string $domain, Lang $language): Translation
    {
        return (new Translation())
            ->setKey($id)
            ->setTranslation($translation)
            ->setDomain($domain)
            ->setLang($language);
    }
}
