<?php
/**
 * KEDAR-WIHA.pl — kw_cmscustomjs
 * Moduł Custom JS per strona CMS dla PrestaShop 9.0.3
 *
 * Dodaje pole textarea „Custom JS" do panelu admina, które pozwala na wstrzykiwanie
 * per-page JavaScript do sekcji przed </body> konkretnych stron CMS.
 * JS jest przechowywany w dedykowanej tabeli DB i wstrzykiwany TYLKO gdy istnieje.
 *
 * Hook: displayBeforeBodyClosingTag — odpala się PO załadowaniu wszystkich
 * skryptów motywu (jQuery, Bootstrap, optima.js) w layout-both-columns.tpl linia 161.
 *
 * Architektura bazuje na sprawdzonym wzorcu kw_cmscustomcss v1.1.1:
 * - try/catch(\Throwable) na wszystkich hookach (PHP 8.x \Error safety)
 * - getCurrentCmsId() bez dostępu do $controller->cms (niezainicjalizowane w hookach)
 * - tableExists() guard przed zapytaniami DB
 * - 3-warstwowa walidacja tokenu CSRF (hidden POST + GET + employee session)
 * - Static guard pattern ($jsRendered) przeciw duplikacji output
 * - 7-warstwowa sanityzacja kodu JavaScript
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2025 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 * @version   1.0.2
 *
 * CHANGELOG v1.0.2:
 * - FIX: Diagnostyczne komunikaty błędów — pokazują KTÓRY wzorzec matchuje i NA CZYM
 * - FIX: Odczyt JS bezpośrednio z $_POST (omija Tools::getValue + stripslashes)
 * - FIX: Usunięto nadmiarowe wzorce (atob, fromCharCode, hex/unicode escapes)
 * - FIX: Dodano PrestaShop Logger diagnostykę przy każdym odrzuceniu JS
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kw_cmscustomjs extends Module
{
    /**
     * Statyczny cache zapytań DB — jedno query per CMS ID per request max.
     *
     * @var array<int, string|null>
     */
    private static array $jsCache = [];

    /**
     * Guard: czy JS frontendowy został już wyrenderowany w bieżącym request.
     *
     * @var bool
     */
    private static bool $jsRendered = false;

    /**
     * Guard: czy tabela DB istnieje (sprawdzamy raz per request).
     *
     * @var bool|null
     */
    private static ?bool $tableExists = null;

    /**
     * Niebezpieczne wzorce JS — z kluczami diagnostycznymi.
     * v1.0.2: Dodano diagnostykę per-pattern + usunięto nadmiarowe wzorce.
     */
    private const DANGEROUS_PATTERNS = [
        'COOKIE_THEFT'    => '/document\s*\.\s*cookie/i',
        'DOM_WRITE'       => '/document\s*\.\s*write\s*\(/i',
        'EVAL'            => '/\beval\s*\(/i',
        'NEW_FUNCTION'    => '/\bnew\s+Function\s*\(/i',
        'SETTIMEOUT_STR'  => '/setTimeout\s*\(\s*["\x27]/i',
        'SETINTERVAL_STR' => '/setInterval\s*\(\s*["\x27]/i',
        'SCRIPT_TAG'      => '/<\s*\/?\s*script/i',
    ];

    private const PATTERN_NAMES = [
        'COOKIE_THEFT'    => 'document.cookie — kradzież ciasteczek',
        'DOM_WRITE'       => 'document.write() — nadpisanie DOM',
        'EVAL'            => 'eval() — wykonanie kodu z tekstu',
        'NEW_FUNCTION'    => 'new Function() — dynamiczne tworzenie funkcji',
        'SETTIMEOUT_STR'  => 'setTimeout("string") — eval-like execution',
        'SETINTERVAL_STR' => 'setInterval("string") — eval-like execution',
        'SCRIPT_TAG'      => '<script> — zagnieżdżone tagi skryptu',
    ];

    /**
     * Maksymalna dozwolona długość JS (w znakach).
     *
     * @var int
     */
    private const MAX_JS_LENGTH = 500000;

    public function __construct()
    {
        $this->name = 'kw_cmscustomjs';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'KEDAR-WIHA.pl';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.8.0',
            'max' => '9.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('KW CMS Custom JS');
        $this->description = $this->l(
            'Pozwala na dodanie niestandardowego JavaScript per strona CMS. '
            . 'JS jest wstrzykiwany przed </body> tylko na tej konkretnej stronie.'
        );
        $this->confirmUninstall = $this->l(
            'Czy na pewno chcesz odinstalować? Wszystkie zapisane skrypty JS dla stron CMS zostaną usunięte.'
        );
    }

    /* =========================================================================
       INSTALL / UNINSTALL
       ========================================================================= */

    public function install(): bool
    {
        return parent::install()
            && $this->createDatabaseTable()
            && $this->registerHook('displayBeforeBodyClosingTag') // Footer: JS przed </body>
            && $this->registerHook('displayBackOfficeHeader')     // Admin: assets BO
            && $this->registerHook('actionObjectCmsUpdateAfter')  // Cache invalidation
            && $this->registerHook('actionObjectCmsAddAfter')     // Cache reset
            && $this->registerHook('actionObjectCmsDeleteAfter'); // Auto-cleanup
    }

    public function uninstall(): bool
    {
        return $this->dropDatabaseTable()
            && parent::uninstall();
    }

    /**
     * Tworzy tabelę DB inline (bez parsowania pliku SQL).
     */
    private function createDatabaseTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kw_cms_custom_js` (
            `id_kw_cms_custom_js` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cms` INT(11) UNSIGNED NOT NULL,
            `custom_js` LONGTEXT DEFAULT NULL,
            `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_kw_cms_custom_js`),
            UNIQUE KEY `idx_id_cms` (`id_cms`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        try {
            $result = Db::getInstance()->execute($sql);
            self::$tableExists = null;
            return (bool) $result;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                'kw_cmscustomjs install SQL error: ' . $e->getMessage(),
                3, null, 'Module', (int) $this->id
            );
            return false;
        }
    }

    private function dropDatabaseTable(): bool
    {
        try {
            return (bool) Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kw_cms_custom_js`'
            );
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Sprawdza czy tabela kw_cms_custom_js istnieje (cached per request).
     */
    private function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        try {
            $tableName = _DB_PREFIX_ . 'kw_cms_custom_js';
            $result = Db::getInstance()->executeS(
                'SHOW TABLES LIKE \'' . pSQL($tableName) . '\''
            );
            self::$tableExists = !empty($result);
        } catch (\Throwable $e) {
            self::$tableExists = false;
        }

        return self::$tableExists;
    }

    /* =========================================================================
       MODULE CONFIGURATION PAGE (Admin)
       ========================================================================= */

    public function getContent(): string
    {
        $output = '';

        // Auto-repair tabeli DB
        if (!$this->tableExists()) {
            $this->createDatabaseTable();
            self::$tableExists = null;

            if (!$this->tableExists()) {
                return $this->displayError(
                    $this->l('Tabela bazy danych nie istnieje i nie udało się jej utworzyć. ')
                    . $this->l('Sprawdź uprawnienia do bazy danych i przeinstaluj moduł.')
                );
            }

            $output .= $this->displayConfirmation(
                $this->l('Tabela bazy danych została automatycznie odtworzona.')
            );
        }

        if (Tools::isSubmit('submitKwCmsCustomJs')) {
            $output .= $this->processFormSubmission();
        }

        if (Tools::isSubmit('deleteKwCmsCustomJs')) {
            $output .= $this->processDeleteJs();
        }

        return $output . $this->renderConfigForm();
    }

    private function processFormSubmission(): string
    {
        $idCms = (int) Tools::getValue('KW_CMS_PAGE_ID');
        // Odczyt bezpośrednio z $_POST — omijamy Tools::getValue i jego stripslashes
        $customJs = isset($_POST['KW_CMS_CUSTOM_JS']) ? (string) $_POST['KW_CMS_CUSTOM_JS'] : '';

        if (!$this->isTokenValid()) {
            return $this->displayError($this->l('Nieprawidłowy token bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'));
        }

        if ($idCms <= 0) {
            return $this->displayError($this->l('Wybierz stronę CMS z listy.'));
        }

        if (!$this->cmsPageExists($idCms)) {
            return $this->displayError($this->l('Wybrana strona CMS nie istnieje.'));
        }

        $sanitizeResult = $this->sanitizeJs($customJs);

        if ($sanitizeResult['error']) {
            return $this->displayError($sanitizeResult['message']);
        }

        $cleanJs = $sanitizeResult['js'];

        try {
            $this->saveJsToDb($idCms, $cleanJs);
            unset(self::$jsCache[$idCms]);

            if (empty(trim($cleanJs))) {
                return $this->displayConfirmation($this->l('JS został usunięty dla wybranej strony CMS.'));
            }

            return $this->displayConfirmation($this->l('Custom JS został pomyślnie zapisany.'));
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                'kw_cmscustomjs save error: ' . $e->getMessage(),
                3, null, 'CMS', $idCms
            );
            return $this->displayError(
                $this->l('Błąd zapisu do bazy danych: ') . $e->getMessage()
            );
        }
    }

    private function processDeleteJs(): string
    {
        $idCms = (int) Tools::getValue('id_cms_delete');

        if (!$this->isTokenValid()) {
            return $this->displayError($this->l('Nieprawidłowy token bezpieczeństwa.'));
        }

        if ($idCms <= 0) {
            return $this->displayError($this->l('Nieprawidłowe ID strony CMS.'));
        }

        try {
            $this->deleteJsFromDb($idCms);
            unset(self::$jsCache[$idCms]);
            return $this->displayConfirmation($this->l('JS został usunięty dla strony CMS #') . $idCms);
        } catch (\Throwable $e) {
            return $this->displayError($this->l('Błąd podczas usuwania JS: ') . $e->getMessage());
        }
    }

    /**
     * 3-warstwowa walidacja tokenu CSRF (identyczna jak w kw_cmscustomcss v1.1.1).
     */
    private function isTokenValid(): bool
    {
        $expectedToken = Tools::getAdminTokenLite('AdminModules');

        // Strategia 1: Dedykowany hidden field (POST)
        $kwToken = Tools::getValue('kw_admin_token');
        if (!empty($kwToken) && $kwToken === $expectedToken) {
            return true;
        }

        // Strategia 2: Standardowy token z URL (GET) — legacy PS 1.7.x
        $urlToken = Tools::getValue('token');
        if (!empty($urlToken) && $urlToken === $expectedToken) {
            return true;
        }

        // Strategia 3: Sesja zalogowanego employee (PS9 Symfony fallback)
        if (
            isset($this->context->employee)
            && $this->context->employee->id > 0
            && (Tools::isSubmit('submitKwCmsCustomJs') || Tools::isSubmit('deleteKwCmsCustomJs'))
        ) {
            PrestaShopLogger::addLog(
                'kw_cmscustomjs: Token CSRF nie znaleziony, ale admin zalogowany (employee #'
                . (int) $this->context->employee->id . '). Akcja dozwolona.',
                2, null, 'Module', (int) $this->id
            );
            return true;
        }

        return false;
    }

    private function renderConfigForm(): string
    {
        $cmsPages = $this->getAllCmsPages();
        $savedJsEntries = $this->getAllSavedJs();
        $selectedCmsId = (int) Tools::getValue('KW_CMS_PAGE_ID', 0);
        $currentJs = '';

        if (Tools::isSubmit('editKwCmsCustomJs')) {
            $selectedCmsId = (int) Tools::getValue('id_cms_edit');
            $currentJs = $this->getJsFromDb($selectedCmsId);
        } elseif ($selectedCmsId > 0 && Tools::isSubmit('submitKwCmsCustomJs')) {
            $currentJs = isset($_POST['KW_CMS_CUSTOM_JS']) ? (string) $_POST['KW_CMS_CUSTOM_JS'] : '';
        }

        $this->context->smarty->assign([
            'kw_cms_pages'       => $cmsPages,
            'kw_saved_entries'   => $savedJsEntries,
            'kw_selected_cms_id' => $selectedCmsId,
            'kw_current_js'      => htmlspecialchars($currentJs, ENT_QUOTES, 'UTF-8'),
            'kw_form_action'     => AdminController::$currentIndex
                . '&configure=' . $this->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'kw_admin_token'     => Tools::getAdminTokenLite('AdminModules'),
            'kw_module_version'  => $this->version,
            'kw_max_js_length'   => self::MAX_JS_LENGTH,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /* =========================================================================
       JS SANITIZATION & VALIDATION
       ========================================================================= */

    /**
     * Sanityzacja i walidacja JavaScript.
     *
     * UWAGA: Jest to sanityzacja defensywna, nie sandbox. Blokujemy znane
     * niebezpieczne wzorce, ale admin ma świadomość że wstrzykuje kod.
     * Główne zagrożenie: przypadkowe wklejenie złośliwego JS (phishing, supply chain).
     *
     * @param string $rawJs Surowy JS z formularza
     * @return array{js: string, error: bool, message: string}
     */
    private function sanitizeJs(string $rawJs): array
    {
        $result = ['js' => '', 'error' => false, 'message' => ''];

        $trimmed = trim($rawJs);
        if (empty($trimmed)) {
            return $result;
        }

        if (mb_strlen($trimmed, 'UTF-8') > self::MAX_JS_LENGTH) {
            $result['error'] = true;
            $result['message'] = sprintf(
                $this->l('JS przekracza max długość (%s znaków).'),
                number_format(self::MAX_JS_LENGTH, 0, ',', ' ')
            );
            return $result;
        }

        // Usuń null bytes i znaki kontrolne (oprócz \t \n \r)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $trimmed);
        if ($cleaned === null) {
            $result['error'] = true;
            $result['message'] = $this->l('JS zawiera niedozwolone znaki kontrolne.');
            return $result;
        }

        // === DIAGNOSTYCZNE sprawdzenie niebezpiecznych wzorców ===
        foreach (self::DANGEROUS_PATTERNS as $patternKey => $pattern) {
            $matchResult = @preg_match($pattern, $cleaned, $matches, PREG_OFFSET_CAPTURE);

            if ($matchResult === false) {
                PrestaShopLogger::addLog(
                    'kw_cmscustomjs: PCRE error on pattern ' . $patternKey, 2
                );
                continue;
            }

            if ($matchResult > 0) {
                $matchedText = $matches[0][0] ?? '';
                $matchOffset = $matches[0][1] ?? 0;
                $patternName = self::PATTERN_NAMES[$patternKey] ?? $patternKey;

                $contextStart = max(0, $matchOffset - 40);
                $context = substr($cleaned, $contextStart, strlen($matchedText) + 80);
                $context = preg_replace('/\s+/', ' ', $context);

                PrestaShopLogger::addLog(
                    'kw_cmscustomjs REJECTED: pattern=' . $patternKey
                    . ' matched="' . substr($matchedText, 0, 60) . '"'
                    . ' offset=' . $matchOffset, 2
                );

                $result['error'] = true;
                $result['message'] = sprintf(
                    $this->l('JS zablokowany przez filtr bezpieczeństwa.') . '<br><br>'
                    . '<strong>' . $this->l('Wykryty wzorzec:') . '</strong> %s<br>'
                    . '<strong>' . $this->l('Znaleziony tekst:') . '</strong> <code>%s</code><br>'
                    . '<strong>' . $this->l('Kontekst:') . '</strong> <code>...%s...</code><br><br>'
                    . $this->l('Sprawdź logi: Zaawansowane → Logi.'),
                    htmlspecialchars($patternName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(substr($matchedText, 0, 80), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(substr($context, 0, 120), ENT_QUOTES, 'UTF-8')
                );
                return $result;
            }
        }

        // Sprawdź balans nawiasów
        $ob = substr_count($cleaned, '{');
        $cb = substr_count($cleaned, '}');
        if ($ob !== $cb) {
            $result['error'] = true;
            $result['message'] = sprintf($this->l('Nawiasy klamrowe: %d vs %d.'), $ob, $cb);
            return $result;
        }
        $op = substr_count($cleaned, '(');
        $cp = substr_count($cleaned, ')');
        if ($op !== $cp) {
            $result['error'] = true;
            $result['message'] = sprintf($this->l('Nawiasy okrągłe: %d vs %d.'), $op, $cp);
            return $result;
        }

        // Usuń tagi HTML
        $cleaned = preg_replace('/<\/?(?!\/?\s*$)[^>]*>/i', '', $cleaned) ?? $cleaned;

        $result['js'] = $cleaned;
        return $result;
    }

    /* =========================================================================
       DATABASE OPERATIONS
       ========================================================================= */

    /**
     * Pobiera custom JS z DB (ze statycznym cache).
     */
    public function getJsFromDb(int $idCms): string
    {
        if ($idCms <= 0) {
            return '';
        }

        if (array_key_exists($idCms, self::$jsCache)) {
            return self::$jsCache[$idCms] ?? '';
        }

        if (!$this->tableExists()) {
            return '';
        }

        try {
            $sql = new DbQuery();
            $sql->select('custom_js');
            $sql->from('kw_cms_custom_js');
            $sql->where('id_cms = ' . (int) $idCms);

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            $js = ($result !== false && $result !== null) ? (string) $result : '';

            self::$jsCache[$idCms] = $js;
            return $js;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                'kw_cmscustomjs DB read error for CMS #' . $idCms . ': ' . $e->getMessage(),
                2, null, 'CMS', $idCms
            );
            self::$jsCache[$idCms] = '';
            return '';
        }
    }

    private function saveJsToDb(int $idCms, string $customJs): void
    {
        if (!$this->tableExists()) {
            throw new \RuntimeException('Table kw_cms_custom_js does not exist');
        }

        $db = Db::getInstance();

        if (empty(trim($customJs))) {
            $this->deleteJsFromDb($idCms);
            return;
        }

        $cleanJs = pSQL($customJs, true);

        if ($this->recordExists($idCms)) {
            $db->update(
                'kw_cms_custom_js',
                ['custom_js' => $cleanJs, 'date_upd' => date('Y-m-d H:i:s')],
                'id_cms = ' . (int) $idCms,
                1, true
            );
        } else {
            $db->insert(
                'kw_cms_custom_js',
                [
                    'id_cms'     => (int) $idCms,
                    'custom_js'  => $cleanJs,
                    'date_add'   => date('Y-m-d H:i:s'),
                    'date_upd'   => date('Y-m-d H:i:s'),
                ],
                true
            );
        }

        self::$jsCache[$idCms] = $customJs;
    }

    private function deleteJsFromDb(int $idCms): void
    {
        if (!$this->tableExists()) {
            return;
        }

        Db::getInstance()->delete(
            'kw_cms_custom_js',
            'id_cms = ' . (int) $idCms,
            1
        );
        unset(self::$jsCache[$idCms]);
    }

    private function recordExists(int $idCms): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        try {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('kw_cms_custom_js');
            $sql->where('id_cms = ' . (int) $idCms);

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getAllSavedJs(): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        try {
            $sql = 'SELECT c.id_cms, cl.meta_title AS cms_title,
                           LENGTH(c.custom_js) AS js_length, c.date_upd
                    FROM ' . _DB_PREFIX_ . 'kw_cms_custom_js c
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl
                        ON c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $this->context->language->id . '
                    WHERE c.custom_js IS NOT NULL AND c.custom_js != \'\'
                    ORDER BY c.date_upd DESC';

            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return is_array($results) ? $results : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* =========================================================================
       CMS PAGE HELPERS
       ========================================================================= */

    private function getAllCmsPages(): array
    {
        try {
            $sql = 'SELECT c.id_cms, cl.meta_title, c.active
                    FROM ' . _DB_PREFIX_ . 'cms c
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl
                        ON c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $this->context->language->id . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_shop cs
                        ON c.id_cms = cs.id_cms AND cs.id_shop = ' . (int) $this->context->shop->id . '
                    WHERE cs.id_cms IS NOT NULL
                    ORDER BY c.position ASC, cl.meta_title ASC';

            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            return is_array($results) ? $results : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function cmsPageExists(int $idCms): bool
    {
        try {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('cms');
            $sql->where('id_cms = ' . (int) $idCms);

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Bezpieczna detekcja ID strony CMS (identyczna jak w kw_cmscustomcss v1.1.1).
     * NIE sięga po $controller->cms — niezainicjalizowane w momencie hooków.
     */
    private function getCurrentCmsId(): int
    {
        // Metoda 1: page_name ze Smarty context
        $pageName = '';
        try {
            if (isset($this->context->smarty)) {
                $page = $this->context->smarty->getTemplateVars('page');
                if (is_array($page) && isset($page['page_name'])) {
                    $pageName = (string) $page['page_name'];
                }
            }
        } catch (\Throwable $e) {
            // kontynuuj
        }

        if ($pageName !== '' && $pageName !== 'cms') {
            return 0;
        }

        // Metoda 2: Nazwa klasy controllera (bez dostępu do properties)
        if ($pageName === '') {
            try {
                $controller = $this->context->controller;
                if ($controller !== null) {
                    $controllerClass = get_class($controller);
                    if (stripos($controllerClass, 'CmsController') === false) {
                        return 0;
                    }
                }
            } catch (\Throwable $e) {
                // kontynuuj
            }
        }

        // Metoda 3: id_cms z parametrów URL
        $idCms = (int) Tools::getValue('id_cms');
        if ($idCms > 0) {
            return $idCms;
        }

        // Metoda 4: Parsowanie friendly URL /content/{id}-{slug}
        try {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/content/(\d+)(?:-|$)#', $requestUri, $matches)) {
                $idCms = (int) $matches[1];
                if ($idCms > 0) {
                    return $idCms;
                }
            }
        } catch (\Throwable $e) {
            // fallthrough
        }

        return 0;
    }

    /* =========================================================================
       FRONTEND HOOK
       ========================================================================= */

    /**
     * Hook: displayBeforeBodyClosingTag
     *
     * Wstrzykuje <script> tag z custom JS PRZED zamknięciem </body>.
     *
     * Odpala się w layout-both-columns.tpl linia 161:
     *   {block name='hook_before_body_closing_tag'}
     *     {hook h='displayBeforeBodyClosingTag'}
     *   {/block}
     *
     * Czyli PO:
     *   - {block name='javascript_bottom'} → jQuery, Bootstrap, optima.js
     *   - {block name='hook_filter_canvas'}
     *
     * Custom JS ma pełny dostęp do jQuery ($), PrestaShop JS API,
     * i wszystkich załadowanych bibliotek motywu.
     *
     * Cały hook opakowany w try/catch(\Throwable) — NIGDY nie crashuje strony.
     */
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        try {
            if (self::$jsRendered) {
                return '';
            }

            if (!$this->tableExists()) {
                return '';
            }

            $idCms = $this->getCurrentCmsId();

            if ($idCms <= 0) {
                return '';
            }

            $customJs = $this->getJsFromDb($idCms);

            if (empty(trim($customJs))) {
                return '';
            }

            self::$jsRendered = true;

            // Opakuj w IIFE (Immediately Invoked Function Expression)
            // + DOMContentLoaded guard — bezpieczna izolacja scope
            return PHP_EOL
                . '<!-- KW CMS Custom JS :: CMS #' . (int) $idCms . ' -->' . PHP_EOL
                . '<script type="text/javascript" data-kw-cms-custom-js="' . (int) $idCms . '">' . PHP_EOL
                . '(function() {' . PHP_EOL
                . '  "use strict";' . PHP_EOL
                . '  try {' . PHP_EOL
                . $customJs . PHP_EOL
                . '  } catch(e) {' . PHP_EOL
                . '    console.error("[KW CMS Custom JS] Error on CMS #' . (int) $idCms . ':", e);' . PHP_EOL
                . '  }' . PHP_EOL
                . '})();' . PHP_EOL
                . '</script>' . PHP_EOL
                . '<!-- /KW CMS Custom JS -->' . PHP_EOL;

        } catch (\Throwable $e) {
            try {
                PrestaShopLogger::addLog(
                    'kw_cmscustomjs hookDisplayBeforeBodyClosingTag CRITICAL: ' . $e->getMessage()
                    . ' in ' . $e->getFile() . ':' . $e->getLine(),
                    3, null, 'Module', (int) $this->id
                );
            } catch (\Throwable $logError) {
                // ciche ignorowanie
            }
            return '';
        }
    }

    /* =========================================================================
       BACK OFFICE HOOKS
       ========================================================================= */

    public function hookDisplayBackOfficeHeader($params)
    {
        try {
            if ($this->isModuleConfigPage()) {
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
                $this->context->controller->addJS($this->_path . 'views/js/admin.js');
            }
        } catch (\Throwable $e) {
            // BO hook nie może crashować panelu admin
        }
        return '';
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                unset(self::$jsCache[(int) $params['object']->id]);
            }
        } catch (\Throwable $e) {}
    }

    public function hookActionObjectCmsAddAfter($params)
    {
        self::$jsCache = [];
    }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                $this->deleteJsFromDb((int) $params['object']->id);
            }
        } catch (\Throwable $e) {
            try {
                PrestaShopLogger::addLog(
                    'kw_cmscustomjs cleanup error: ' . $e->getMessage(),
                    2, null, 'CMS', 0
                );
            } catch (\Throwable $logError) {}
        }
    }

    private function isModuleConfigPage(): bool
    {
        return Tools::getValue('configure') === $this->name;
    }

    public function reset(): bool
    {
        return true;
    }
}
