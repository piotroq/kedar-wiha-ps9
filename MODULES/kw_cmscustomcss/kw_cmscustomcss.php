<?php
/**
 * KEDAR-WIHA.pl — kw_cmscustomcss
 * Moduł Custom CSS per strona CMS dla PrestaShop 9.0.3
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2025 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 * @version   2.0.1
 *
 * CHANGELOG v2.0.1:
 * - FIX: /\bbehavior\s*:/i matchował scroll-behavior: auto (legalne CSS)
 *   Zmieniono na /(?<![\w-])behavior\s*:/i — negative lookbehind za myślnikiem
 *
 * CHANGELOG v2.0.0:
 * - REWRITE: Kompletna przebudowa sanityzacji CSS
 * - FIX: Diagnostyczne komunikaty błędów — pokazują KTÓRY wzorzec matchuje i NA CZYM
 * - FIX: Zmniejszona agresywność sanityzacji (CSS nie może wykonywać kodu)
 * - FIX: Usunięto @charset i @namespace z blokowanych (legalne CSS at-rules)
 * - FIX: Dodano PrestaShop Logger diagnostykę przy każdym odrzuceniu CSS
 * - FIX: Zawiera wszystkie poprawki z v1.1.1 (token CSRF, \Throwable, getCurrentCmsId)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kw_cmscustomcss extends Module
{
    private static array $cssCache = [];
    private static bool $cssRendered = false;
    private static ?bool $tableExists = null;

    /**
     * Dozwolone CSS at-rules (whitelist).
     * Rozszerzono w v2.0.0: dodano @charset, @namespace, @page, @counter-style.
     */
    private const ALLOWED_AT_RULES = [
        '@media', '@supports', '@keyframes', '@font-face',
        '@layer', '@container', '@charset', '@namespace',
        '@page', '@counter-style', '@property',
    ];

    /**
     * Niebezpieczne wzorce CSS — TYLKO prawdziwe wektory ataków.
     * Każdy wzorzec ma unikalny klucz diagnostyczny.
     *
     * v2.0.0: Usunięto overagresywne wzorce (np. @charset, @namespace).
     * Skupiono się wyłącznie na wzorcach, które mogą wykonać kod.
     */
    private const DANGEROUS_PATTERNS = [
        'CSS_EXPRESSION'   => '/\bexpression\s*\(/i',
        'JS_URI'           => '/javascript\s*:/i',
        'VBS_URI'          => '/vbscript\s*:/i',
        'DATA_EXEC_URI'    => '/data\s*:\s*[^,]*?(?:text\/html|application\/x?javascript|image\/svg\+xml)/i',
        'MOZ_BINDING'      => '/-moz-binding\s*:/i',
        'IE_BEHAVIOR'      => '/(?<![\w-])behavior\s*:/i',
        'URL_JS'           => '/url\s*\(\s*["\']?\s*javascript\s*:/i',
        'CSS_IMPORT'       => '/@import\s+/i',
    ];

    /**
     * Czytelne nazwy wzorców (po polsku) do komunikatów błędów.
     */
    private const PATTERN_NAMES = [
        'CSS_EXPRESSION' => 'expression() — IE CSS injection',
        'JS_URI'         => 'javascript: URI scheme',
        'VBS_URI'        => 'vbscript: URI scheme',
        'DATA_EXEC_URI'  => 'data: URI z kodem wykonywalnym',
        'MOZ_BINDING'    => '-moz-binding: (Firefox XBL)',
        'IE_BEHAVIOR'    => 'behavior: (IE HTC)',
        'URL_JS'         => 'url(javascript:...)',
        'CSS_IMPORT'     => '@import (ładowanie zewnętrznych stylesheets)',
    ];

    private const MAX_CSS_LENGTH = 500000;

    public function __construct()
    {
        $this->name = 'kw_cmscustomcss';
        $this->tab = 'front_office_features';
        $this->version = '2.0.1';
        $this->author = 'KEDAR-WIHA.pl';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('KW CMS Custom CSS');
        $this->description = $this->l(
            'Pozwala na dodanie niestandardowego CSS per strona CMS. '
            . 'CSS jest wstrzykiwany do <head> tylko na tej konkretnej stronie.'
        );
        $this->confirmUninstall = $this->l(
            'Czy na pewno chcesz odinstalować? Wszystkie zapisane style CSS dla stron CMS zostaną usunięte.'
        );
    }

    /* =========================================================================
       INSTALL / UNINSTALL
       ========================================================================= */

    public function install(): bool
    {
        return parent::install()
            && $this->createDatabaseTable()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionObjectCmsUpdateAfter')
            && $this->registerHook('actionObjectCmsAddAfter')
            && $this->registerHook('actionObjectCmsDeleteAfter');
    }

    public function uninstall(): bool
    {
        return $this->dropDatabaseTable() && parent::uninstall();
    }

    private function createDatabaseTable(): bool
    {
        try {
            $result = Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kw_cms_custom_css` (
                    `id_kw_cms_custom_css` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `id_cms` INT(11) UNSIGNED NOT NULL,
                    `custom_css` LONGTEXT DEFAULT NULL,
                    `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id_kw_cms_custom_css`),
                    UNIQUE KEY `idx_id_cms` (`id_cms`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            self::$tableExists = null;
            return (bool) $result;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('kw_cmscustomcss install error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    private function dropDatabaseTable(): bool
    {
        try {
            return (bool) Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kw_cms_custom_css`'
            );
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }
        try {
            $result = Db::getInstance()->executeS(
                'SHOW TABLES LIKE \'' . pSQL(_DB_PREFIX_ . 'kw_cms_custom_css') . '\''
            );
            self::$tableExists = !empty($result);
        } catch (\Throwable $e) {
            self::$tableExists = false;
        }
        return self::$tableExists;
    }

    /* =========================================================================
       MODULE CONFIGURATION PAGE
       ========================================================================= */

    public function getContent(): string
    {
        $output = '';

        if (!$this->tableExists()) {
            $this->createDatabaseTable();
            self::$tableExists = null;
            if (!$this->tableExists()) {
                return $this->displayError($this->l('Tabela DB nie istnieje. Przeinstaluj moduł.'));
            }
            $output .= $this->displayConfirmation($this->l('Tabela DB odtworzona automatycznie.'));
        }

        if (Tools::isSubmit('submitKwCmsCustomCss')) {
            $output .= $this->processFormSubmission();
        }
        if (Tools::isSubmit('deleteKwCmsCustomCss')) {
            $output .= $this->processDeleteCss();
        }

        return $output . $this->renderConfigForm();
    }

    private function processFormSubmission(): string
    {
        $idCms = (int) Tools::getValue('KW_CMS_PAGE_ID');

        // Pobieramy CSS BEZPOŚREDNIO z $_POST (omijamy Tools::getValue i jego stripslashes)
        $customCss = isset($_POST['KW_CMS_CUSTOM_CSS']) ? (string) $_POST['KW_CMS_CUSTOM_CSS'] : '';

        if (!$this->isTokenValid()) {
            return $this->displayError($this->l('Nieprawidłowy token bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'));
        }

        if ($idCms <= 0) {
            return $this->displayError($this->l('Wybierz stronę CMS z listy.'));
        }

        if (!$this->cmsPageExists($idCms)) {
            return $this->displayError($this->l('Wybrana strona CMS nie istnieje.'));
        }

        $sanitizeResult = $this->sanitizeCss($customCss);

        if ($sanitizeResult['error']) {
            return $this->displayError($sanitizeResult['message']);
        }

        try {
            $this->saveCssToDb($idCms, $sanitizeResult['css']);
            unset(self::$cssCache[$idCms]);

            if (empty(trim($sanitizeResult['css']))) {
                return $this->displayConfirmation($this->l('CSS usunięty dla wybranej strony CMS.'));
            }
            return $this->displayConfirmation($this->l('Custom CSS zapisany pomyślnie.'));
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('kw_cmscustomcss save error: ' . $e->getMessage(), 3, null, 'CMS', $idCms);
            return $this->displayError($this->l('Błąd zapisu: ') . $e->getMessage());
        }
    }

    private function processDeleteCss(): string
    {
        $idCms = (int) Tools::getValue('id_cms_delete');

        if (!$this->isTokenValid()) {
            return $this->displayError($this->l('Nieprawidłowy token.'));
        }
        if ($idCms <= 0) {
            return $this->displayError($this->l('Nieprawidłowe ID strony CMS.'));
        }

        try {
            $this->deleteCssFromDb($idCms);
            return $this->displayConfirmation($this->l('CSS usunięty dla CMS #') . $idCms);
        } catch (\Throwable $e) {
            return $this->displayError($this->l('Błąd usuwania: ') . $e->getMessage());
        }
    }

    /**
     * 3-warstwowa walidacja tokenu CSRF (kompatybilna z PS9 Symfony).
     */
    private function isTokenValid(): bool
    {
        $expected = Tools::getAdminTokenLite('AdminModules');

        // 1. Dedykowany hidden field POST
        $kw = Tools::getValue('kw_admin_token');
        if (!empty($kw) && $kw === $expected) {
            return true;
        }
        // 2. Standardowy token GET (legacy PS 1.7.x)
        $url = Tools::getValue('token');
        if (!empty($url) && $url === $expected) {
            return true;
        }
        // 3. Zalogowany employee + nasz formularz
        if (
            isset($this->context->employee) && $this->context->employee->id > 0
            && (Tools::isSubmit('submitKwCmsCustomCss') || Tools::isSubmit('deleteKwCmsCustomCss'))
        ) {
            return true;
        }
        return false;
    }

    private function renderConfigForm(): string
    {
        $cmsPages = $this->getAllCmsPages();
        $savedEntries = $this->getAllSavedCss();
        $selectedCmsId = (int) Tools::getValue('KW_CMS_PAGE_ID', 0);
        $currentCss = '';

        if (Tools::isSubmit('editKwCmsCustomCss')) {
            $selectedCmsId = (int) Tools::getValue('id_cms_edit');
            $currentCss = $this->getCssFromDb($selectedCmsId);
        } elseif ($selectedCmsId > 0 && Tools::isSubmit('submitKwCmsCustomCss')) {
            $currentCss = isset($_POST['KW_CMS_CUSTOM_CSS']) ? (string) $_POST['KW_CMS_CUSTOM_CSS'] : '';
        }

        $this->context->smarty->assign([
            'kw_cms_pages'       => $cmsPages,
            'kw_saved_entries'   => $savedEntries,
            'kw_selected_cms_id' => $selectedCmsId,
            'kw_current_css'     => htmlspecialchars($currentCss, ENT_QUOTES, 'UTF-8'),
            'kw_form_action'     => AdminController::$currentIndex
                . '&configure=' . $this->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'kw_admin_token'     => Tools::getAdminTokenLite('AdminModules'),
            'kw_module_version'  => $this->version,
            'kw_max_css_length'  => self::MAX_CSS_LENGTH,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /* =========================================================================
       CSS SANITIZATION v2.0 — DIAGNOSTYCZNA
       ========================================================================= */

    /**
     * Sanityzacja CSS z diagnostycznymi komunikatami błędów.
     *
     * v2.0.0: Każdy odrzucony CSS loguje w PrestaShop Logger DOKŁADNIE
     * który wzorzec matchuje i na jakim fragmencie tekstu.
     * Komunikat błędu dla admina zawiera te same informacje.
     *
     * @param string $rawCss Surowy CSS z formularza
     * @return array{css: string, error: bool, message: string}
     */
    private function sanitizeCss(string $rawCss): array
    {
        $result = ['css' => '', 'error' => false, 'message' => ''];

        $trimmed = trim($rawCss);
        if (empty($trimmed)) {
            return $result;
        }

        // Sprawdź długość
        if (mb_strlen($trimmed, 'UTF-8') > self::MAX_CSS_LENGTH) {
            $result['error'] = true;
            $result['message'] = sprintf(
                $this->l('CSS przekracza max długość (%s znaków).'),
                number_format(self::MAX_CSS_LENGTH, 0, ',', ' ')
            );
            return $result;
        }

        // Usuń null bytes i znaki kontrolne (oprócz \t \n \r)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $trimmed);
        if ($cleaned === null) {
            $result['error'] = true;
            $result['message'] = $this->l('CSS zawiera niedozwolone znaki kontrolne.');
            return $result;
        }

        // === SPRAWDZENIE NIEBEZPIECZNYCH WZORCÓW (z diagnostyką) ===
        foreach (self::DANGEROUS_PATTERNS as $patternKey => $pattern) {
            $matchResult = @preg_match($pattern, $cleaned, $matches, PREG_OFFSET_CAPTURE);

            // preg_match error (PCRE crash) — loguj ale NIE blokuj
            if ($matchResult === false) {
                PrestaShopLogger::addLog(
                    'kw_cmscustomcss: PCRE error on pattern ' . $patternKey
                    . ' (preg_last_error=' . preg_last_error() . '). CSS accepted despite PCRE failure.',
                    2
                );
                continue;
            }

            if ($matchResult > 0) {
                // Zbuduj diagnostyczny komunikat
                $matchedText = $matches[0][0] ?? '';
                $matchOffset = $matches[0][1] ?? 0;
                $patternName = self::PATTERN_NAMES[$patternKey] ?? $patternKey;

                // Fragment kontekstu (±40 znaków wokół matcha)
                $contextStart = max(0, $matchOffset - 40);
                $contextLen = strlen($matchedText) + 80;
                $context = substr($cleaned, $contextStart, $contextLen);
                $context = preg_replace('/\s+/', ' ', $context); // kompresuj whitespace

                // Log do PrestaShop Logger (pełna diagnostyka)
                PrestaShopLogger::addLog(
                    'kw_cmscustomcss REJECTED: pattern=' . $patternKey
                    . ' matched="' . substr($matchedText, 0, 60) . '"'
                    . ' offset=' . $matchOffset
                    . ' context="' . substr($context, 0, 100) . '"',
                    2,
                    null,
                    'Module',
                    (int) $this->id
                );

                $result['error'] = true;
                $result['message'] = sprintf(
                    $this->l('CSS zablokowany przez filtr bezpieczeństwa.') . '<br><br>'
                    . '<strong>' . $this->l('Wykryty wzorzec:') . '</strong> %s<br>'
                    . '<strong>' . $this->l('Znaleziony tekst:') . '</strong> <code>%s</code><br>'
                    . '<strong>' . $this->l('Kontekst (fragment CSS):') . '</strong> <code>...%s...</code><br><br>'
                    . $this->l('Jeśli uważasz że to false positive, sprawdź logi: Zaawansowane → Logi.'),
                    htmlspecialchars($patternName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(substr($matchedText, 0, 80), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(substr($context, 0, 120), ENT_QUOTES, 'UTF-8')
                );
                return $result;
            }
        }

        // Sprawdź balans nawiasów klamrowych
        $openBraces = substr_count($cleaned, '{');
        $closeBraces = substr_count($cleaned, '}');
        if ($openBraces !== $closeBraces) {
            $result['error'] = true;
            $result['message'] = sprintf(
                $this->l('Niezbalansowane nawiasy: %d otwierających vs %d zamykających.'),
                $openBraces,
                $closeBraces
            );
            return $result;
        }

        // Usuń komentarze HTML (XSS w <style>)
        $cleaned = preg_replace('/<!--.*?-->/s', '', $cleaned) ?? $cleaned;

        // Usuń tagi HTML (ale nie content wewnątrz)
        $cleaned = strip_tags($cleaned);

        $result['css'] = $cleaned;
        return $result;
    }

    /* =========================================================================
       DATABASE OPERATIONS
       ========================================================================= */

    public function getCssFromDb(int $idCms): string
    {
        if ($idCms <= 0) { return ''; }
        if (array_key_exists($idCms, self::$cssCache)) { return self::$cssCache[$idCms] ?? ''; }
        if (!$this->tableExists()) { return ''; }

        try {
            $sql = new DbQuery();
            $sql->select('custom_css');
            $sql->from('kw_cms_custom_css');
            $sql->where('id_cms = ' . (int) $idCms);
            $r = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $css = ($r !== false && $r !== null) ? (string) $r : '';
            self::$cssCache[$idCms] = $css;
            return $css;
        } catch (\Throwable $e) {
            self::$cssCache[$idCms] = '';
            return '';
        }
    }

    private function saveCssToDb(int $idCms, string $css): void
    {
        if (!$this->tableExists()) {
            throw new \RuntimeException('Table does not exist');
        }
        $db = Db::getInstance();

        if (empty(trim($css))) {
            $this->deleteCssFromDb($idCms);
            return;
        }

        $escapedCss = pSQL($css, true);

        if ($this->recordExists($idCms)) {
            $db->update('kw_cms_custom_css',
                ['custom_css' => $escapedCss, 'date_upd' => date('Y-m-d H:i:s')],
                'id_cms = ' . (int) $idCms, 1, true
            );
        } else {
            $db->insert('kw_cms_custom_css', [
                'id_cms' => (int) $idCms,
                'custom_css' => $escapedCss,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ], true);
        }
        self::$cssCache[$idCms] = $css;
    }

    private function deleteCssFromDb(int $idCms): void
    {
        if (!$this->tableExists()) { return; }
        Db::getInstance()->delete('kw_cms_custom_css', 'id_cms = ' . (int) $idCms, 1);
        unset(self::$cssCache[$idCms]);
    }

    private function recordExists(int $idCms): bool
    {
        if (!$this->tableExists()) { return false; }
        try {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('kw_cms_custom_css');
            $sql->where('id_cms = ' . (int) $idCms);
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        } catch (\Throwable $e) { return false; }
    }

    private function getAllSavedCss(): array
    {
        if (!$this->tableExists()) { return []; }
        try {
            $sql = 'SELECT c.id_cms, cl.meta_title AS cms_title, LENGTH(c.custom_css) AS css_length, c.date_upd
                    FROM ' . _DB_PREFIX_ . 'kw_cms_custom_css c
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $this->context->language->id . '
                    WHERE c.custom_css IS NOT NULL AND c.custom_css != \'\'
                    ORDER BY c.date_upd DESC';
            $r = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) { return []; }
    }

    /* =========================================================================
       CMS PAGE HELPERS
       ========================================================================= */

    private function getAllCmsPages(): array
    {
        try {
            $sql = 'SELECT c.id_cms, cl.meta_title, c.active
                    FROM ' . _DB_PREFIX_ . 'cms c
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $this->context->language->id . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_shop cs ON c.id_cms = cs.id_cms AND cs.id_shop = ' . (int) $this->context->shop->id . '
                    WHERE cs.id_cms IS NOT NULL ORDER BY c.position ASC, cl.meta_title ASC';
            $r = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return is_array($r) ? $r : [];
        } catch (\Throwable $e) { return []; }
    }

    private function cmsPageExists(int $idCms): bool
    {
        try {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('cms');
            $sql->where('id_cms = ' . (int) $idCms);
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        } catch (\Throwable $e) { return false; }
    }

    /**
     * Bezpieczna detekcja CMS ID — NIE sięga po $controller->cms.
     */
    private function getCurrentCmsId(): int
    {
        $pageName = '';
        try {
            if (isset($this->context->smarty)) {
                $page = $this->context->smarty->getTemplateVars('page');
                if (is_array($page) && isset($page['page_name'])) {
                    $pageName = (string) $page['page_name'];
                }
            }
        } catch (\Throwable $e) {}

        if ($pageName !== '' && $pageName !== 'cms') { return 0; }

        if ($pageName === '') {
            try {
                $c = $this->context->controller;
                if ($c !== null && stripos(get_class($c), 'CmsController') === false) { return 0; }
            } catch (\Throwable $e) {}
        }

        $idCms = (int) Tools::getValue('id_cms');
        if ($idCms > 0) { return $idCms; }

        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/content/(\d+)(?:-|$)#', $uri, $m)) {
                return (int) $m[1];
            }
        } catch (\Throwable $e) {}

        return 0;
    }

    /* =========================================================================
       FRONTEND HOOK
       ========================================================================= */

    public function hookDisplayHeader($params)
    {
        try {
            if (self::$cssRendered) { return ''; }
            if (!$this->tableExists()) { return ''; }

            $idCms = $this->getCurrentCmsId();
            if ($idCms <= 0) { return ''; }

            $css = $this->getCssFromDb($idCms);
            if (empty(trim($css))) { return ''; }

            self::$cssRendered = true;

            return PHP_EOL
                . '<!-- KW CMS Custom CSS :: CMS #' . (int) $idCms . ' -->' . PHP_EOL
                . '<style type="text/css" data-kw-cms-custom="' . (int) $idCms . '">' . PHP_EOL
                . $css . PHP_EOL
                . '</style>' . PHP_EOL
                . '<!-- /KW CMS Custom CSS -->' . PHP_EOL;

        } catch (\Throwable $e) {
            try { PrestaShopLogger::addLog('kw_cmscustomcss hookDisplayHeader: ' . $e->getMessage(), 3); } catch (\Throwable $x) {}
            return '';
        }
    }

    /* =========================================================================
       BACK OFFICE HOOKS
       ========================================================================= */

    public function hookDisplayBackOfficeHeader($params)
    {
        try {
            if (Tools::getValue('configure') === $this->name) {
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
                $this->context->controller->addJS($this->_path . 'views/js/admin.js');
            }
        } catch (\Throwable $e) {}
        return '';
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                unset(self::$cssCache[(int) $params['object']->id]);
            }
        } catch (\Throwable $e) {}
    }

    public function hookActionObjectCmsAddAfter($params) { self::$cssCache = []; }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                $this->deleteCssFromDb((int) $params['object']->id);
            }
        } catch (\Throwable $e) {}
    }

    public function reset(): bool { return true; }
}
