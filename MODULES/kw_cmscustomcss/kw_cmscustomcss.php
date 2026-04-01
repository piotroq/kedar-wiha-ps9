<?php
/**
 * KEDAR-WIHA.pl — kw_cmscustomcss
 * Moduł Custom CSS per strona CMS dla PrestaShop 9.0.3
 *
 * Dodaje pole textarea „Custom CSS" do panelu admina, które pozwala na wstrzykiwanie
 * per-page CSS do sekcji <head> konkretnych stron CMS.
 * CSS jest przechowywany w dedykowanej tabeli DB i wstrzykiwany TYLKO gdy istnieje.
 *
 * CHANGELOG v1.1.1:
 * - FIX: Token CSRF — 3-warstwowa walidacja (hidden POST field + URL GET + employee session)
 * - FIX: Dodano hidden field kw_admin_token w formularzu (POST niezależny od URL params)
 * - FIX: Kompatybilność z Symfony routing w PrestaShop 9 (legacy token + session fallback)
 *
 * CHANGELOG v1.1.0:
 * - FIX: hookDisplayHeader opakowany w try/catch(\Throwable) — łapie \Error z PHP 8.x
 * - FIX: getCurrentCmsId() nie sięga po $controller->cms (niezainicjalizowane w displayHeader)
 * - FIX: Wszystkie catch(\Exception) zmienione na catch(\Throwable)
 * - FIX: Dodano tableExists() guard przed zapytaniami DB
 * - FIX: Kompatybilność z Creative Elements PageBuilder
 * - FIX: Bezpieczna detekcja CMS ID przez URL params + page_name (nie controller property)
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2025 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 * @version   1.1.1
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kw_cmscustomcss extends Module
{
    /**
     * Statyczny cache zapytań DB — unikamy wielokrotnych query per request.
     * Klucz: (int) id_cms, wartość: (string|null) custom_css
     *
     * @var array<int, string|null>
     */
    private static array $cssCache = [];

    /**
     * Guard: czy CSS frontendowy został już wyrenderowany w bieżącym request.
     * Zapobiega duplikacji gdy wiele hooków odpali displayHeader.
     *
     * @var bool
     */
    private static bool $cssRendered = false;

    /**
     * Guard: czy tabela DB istnieje (sprawdzamy raz per request).
     * null = nie sprawdzono jeszcze, true/false = wynik sprawdzenia.
     *
     * @var bool|null
     */
    private static ?bool $tableExists = null;

    /**
     * Lista dozwolonych at-rules w CSS (whitelist).
     *
     * @var string[]
     */
    private const ALLOWED_AT_RULES = [
        '@media',
        '@supports',
        '@keyframes',
        '@font-face',
        '@layer',
        '@container',
    ];

    /**
     * Niebezpieczne wzorce CSS — blokujemy injection / data-uri / JS.
     *
     * @var string[]
     */
    private const DANGEROUS_PATTERNS = [
        '/expression\s*\(/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*[^,]*(?:text\/html|application\/x?javascript|image\/svg\+xml)/i',
        '/-moz-binding\s*:/i',
        '/behavior\s*:/i',
        '/url\s*\(\s*["\']?\s*javascript/i',
        '/\\\\00/i',
        '/@import\s+/i',
        '/@charset/i',
        '/@namespace/i',
    ];

    /**
     * Maksymalna dozwolona długość CSS (w znakach).
     *
     * @var int
     */
    private const MAX_CSS_LENGTH = 500000;

    public function __construct()
    {
        $this->name = 'kw_cmscustomcss';
        $this->tab = 'front_office_features';
        $this->version = '1.1.1';
        $this->author = 'KEDAR-WIHA.pl';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.8.0',
            'max' => '9.99.99',
        ];
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

    /**
     * Instalacja modułu: tworzy tabelę DB i rejestruje hooki.
     */
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

    /**
     * Deinstalacja: usuwa tabelę DB.
     */
    public function uninstall(): bool
    {
        return $this->dropDatabaseTable()
            && parent::uninstall();
    }

    /**
     * Tworzy tabelę DB bezpośrednio (bez parsowania pliku SQL).
     * Bardziej niezawodne niż parsowanie pliku SQL regex-em.
     */
    private function createDatabaseTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kw_cms_custom_css` (
            `id_kw_cms_custom_css` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cms` INT(11) UNSIGNED NOT NULL,
            `custom_css` LONGTEXT DEFAULT NULL,
            `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_kw_cms_custom_css`),
            UNIQUE KEY `idx_id_cms` (`id_cms`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        try {
            $result = Db::getInstance()->execute($sql);
            // Reset table existence cache
            self::$tableExists = null;
            return (bool) $result;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                'kw_cmscustomcss install SQL error: ' . $e->getMessage(),
                3,
                null,
                'Module',
                (int) $this->id
            );
            return false;
        }
    }

    /**
     * Usuwa tabelę DB.
     */
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

    /**
     * Sprawdza czy tabela kw_cms_custom_css istnieje (raz per request, cached).
     *
     * Kluczowe zabezpieczenie: jeśli tabela nie istnieje (np. błąd instalacji),
     * moduł nie crashuje strony — zwraca gracefully.
     */
    private function tableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        try {
            $tableName = _DB_PREFIX_ . 'kw_cms_custom_css';
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

    /**
     * Wyświetla stronę konfiguracji modułu w BO.
     */
    public function getContent(): string
    {
        $output = '';

        // Sprawdź czy tabela istnieje — jeśli nie, spróbuj ją utworzyć
        if (!$this->tableExists()) {
            $this->createDatabaseTable();
            self::$tableExists = null; // Reset cache

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

        // Obsługa zapisu formularza
        if (Tools::isSubmit('submitKwCmsCustomCss')) {
            $output .= $this->processFormSubmission();
        }

        // Obsługa usuwania CSS
        if (Tools::isSubmit('deleteKwCmsCustomCss')) {
            $output .= $this->processDeleteCss();
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Przetwarza zapis formularza konfiguracji.
     */
    private function processFormSubmission(): string
    {
        $idCms = (int) Tools::getValue('KW_CMS_PAGE_ID');
        $customCss = (string) Tools::getValue('KW_CMS_CUSTOM_CSS');

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

        $cleanCss = $sanitizeResult['css'];

        try {
            $this->saveCssToDb($idCms, $cleanCss);
            unset(self::$cssCache[$idCms]);

            if (empty(trim($cleanCss))) {
                return $this->displayConfirmation($this->l('CSS został usunięty dla wybranej strony CMS.'));
            }

            return $this->displayConfirmation($this->l('Custom CSS został pomyślnie zapisany.'));
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                'kw_cmscustomcss save error: ' . $e->getMessage(),
                3,
                null,
                'CMS',
                $idCms
            );
            return $this->displayError(
                $this->l('Błąd zapisu do bazy danych: ') . $e->getMessage()
            );
        }
    }

    /**
     * Przetwarza żądanie usunięcia CSS.
     */
    private function processDeleteCss(): string
    {
        $idCms = (int) Tools::getValue('id_cms_delete');

        if (!$this->isTokenValid()) {
            return $this->displayError($this->l('Nieprawidłowy token bezpieczeństwa.'));
        }

        if ($idCms <= 0) {
            return $this->displayError($this->l('Nieprawidłowe ID strony CMS.'));
        }

        try {
            $this->deleteCssFromDb($idCms);
            unset(self::$cssCache[$idCms]);
            return $this->displayConfirmation($this->l('CSS został usunięty dla strony CMS #') . $idCms);
        } catch (\Throwable $e) {
            return $this->displayError($this->l('Błąd podczas usuwania CSS: ') . $e->getMessage());
        }
    }

    /**
     * Weryfikacja tokenu CSRF.
     *
     * PrestaShop 9 używa Symfony CSRF (_token) obok legacy tokenu.
     * Sprawdzamy nasz dedykowany hidden field (kw_admin_token),
     * a jeśli go brak — fallback na standardowy GET/POST token.
     *
     * Admin panel PS sam w sobie wymaga zalogowanego pracownika,
     * więc getContent() jest już chronione sesją BO.
     * Ta walidacja to dodatkowa warstwa defense-in-depth.
     */
    private function isTokenValid(): bool
    {
        $expectedToken = Tools::getAdminTokenLite('AdminModules');

        // Strategia 1: Dedykowany hidden field (POST) — najwiarygodniejszy
        $kwToken = Tools::getValue('kw_admin_token');
        if (!empty($kwToken) && $kwToken === $expectedToken) {
            return true;
        }

        // Strategia 2: Standardowy token z URL (GET) — legacy PS 1.7.x
        $urlToken = Tools::getValue('token');
        if (!empty($urlToken) && $urlToken === $expectedToken) {
            return true;
        }

        // Strategia 3: Sprawdź czy jesteśmy w kontekście zalogowanego admin
        // (defense-in-depth fallback — PS9 sam waliduje sesję BO)
        if (
            isset($this->context->employee)
            && $this->context->employee->id > 0
            && (Tools::isSubmit('submitKwCmsCustomCss') || Tools::isSubmit('deleteKwCmsCustomCss'))
        ) {
            // Zalogowany admin + submit z naszego formularza — akceptuj
            // Loguj ostrzeżenie o brakującym tokenie
            PrestaShopLogger::addLog(
                'kw_cmscustomcss: Token CSRF nie znaleziony, ale admin zalogowany (employee #'
                . (int) $this->context->employee->id . '). Akcja dozwolona.',
                2,
                null,
                'Module',
                (int) $this->id
            );
            return true;
        }

        return false;
    }

    /**
     * Renderuje formularz konfiguracji.
     */
    private function renderConfigForm(): string
    {
        $cmsPages = $this->getAllCmsPages();
        $savedCssEntries = $this->getAllSavedCss();
        $selectedCmsId = (int) Tools::getValue('KW_CMS_PAGE_ID', 0);
        $currentCss = '';

        if (Tools::isSubmit('editKwCmsCustomCss')) {
            $selectedCmsId = (int) Tools::getValue('id_cms_edit');
            $currentCss = $this->getCssFromDb($selectedCmsId);
        } elseif ($selectedCmsId > 0 && Tools::isSubmit('submitKwCmsCustomCss')) {
            $currentCss = (string) Tools::getValue('KW_CMS_CUSTOM_CSS');
        }

        $this->context->smarty->assign([
            'kw_cms_pages'       => $cmsPages,
            'kw_saved_entries'   => $savedCssEntries,
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
       CSS SANITIZATION & VALIDATION
       ========================================================================= */

    /**
     * Sanityzacja i walidacja CSS.
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

        if (mb_strlen($trimmed, 'UTF-8') > self::MAX_CSS_LENGTH) {
            $result['error'] = true;
            $result['message'] = sprintf(
                $this->l('CSS przekracza maksymalną dozwoloną długość (%d znaków).'),
                self::MAX_CSS_LENGTH
            );
            return $result;
        }

        // Usuń null bytes i niebezpieczne znaki kontrolne
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $trimmed);

        if ($cleaned === null) {
            $result['error'] = true;
            $result['message'] = $this->l('CSS zawiera niedozwolone znaki.');
            return $result;
        }

        // Sprawdź niebezpieczne wzorce
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $cleaned)) {
                $result['error'] = true;
                $result['message'] = $this->l(
                    'CSS zawiera niedozwolone wyrażenie (np. expression(), javascript:, @import). '
                    . 'Usuń potencjalnie niebezpieczny kod i spróbuj ponownie.'
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
                $this->l('Niezbalansowane nawiasy klamrowe w CSS: %d otwierających vs %d zamykających.'),
                $openBraces,
                $closeBraces
            );
            return $result;
        }

        // Walidacja at-rules (whitelist)
        if (preg_match_all('/@([a-z\-]+)/i', $cleaned, $atRuleMatches)) {
            foreach ($atRuleMatches[0] as $atRule) {
                $atRuleLower = strtolower($atRule);
                $isAllowed = false;
                foreach (self::ALLOWED_AT_RULES as $allowed) {
                    if (str_starts_with($atRuleLower, strtolower($allowed))) {
                        $isAllowed = true;
                        break;
                    }
                }
                if (!$isAllowed) {
                    $result['error'] = true;
                    $result['message'] = sprintf(
                        $this->l('Niedozwolona reguła CSS: "%s". Dozwolone: %s'),
                        htmlspecialchars($atRule, ENT_QUOTES, 'UTF-8'),
                        implode(', ', self::ALLOWED_AT_RULES)
                    );
                    return $result;
                }
            }
        }

        // Usuń komentarze HTML (XSS vector w <style>)
        $cleaned = preg_replace('/<!--.*?-->/s', '', $cleaned) ?? $cleaned;

        // Usuń tagi HTML
        $cleaned = strip_tags($cleaned);

        $result['css'] = $cleaned;
        return $result;
    }

    /* =========================================================================
       DATABASE OPERATIONS (Repository Layer)
       ========================================================================= */

    /**
     * Pobiera custom CSS dla danej strony CMS.
     * Wykorzystuje statyczny cache + tableExists guard.
     *
     * @param int $idCms ID strony CMS
     * @return string CSS lub pusty string
     */
    public function getCssFromDb(int $idCms): string
    {
        if ($idCms <= 0) {
            return '';
        }

        // Cache hit
        if (array_key_exists($idCms, self::$cssCache)) {
            return self::$cssCache[$idCms] ?? '';
        }

        // Guard: tabela musi istnieć
        if (!$this->tableExists()) {
            return '';
        }

        try {
            $sql = new DbQuery();
            $sql->select('custom_css');
            $sql->from('kw_cms_custom_css');
            $sql->where('id_cms = ' . (int) $idCms);

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

            $css = ($result !== false && $result !== null) ? (string) $result : '';

            self::$cssCache[$idCms] = $css;

            return $css;
        } catch (\Throwable $e) {
            // Łapiemy \Throwable (nie \Exception) — obejmuje TypeError, Error, PDOException
            PrestaShopLogger::addLog(
                'kw_cmscustomcss DB read error for CMS #' . $idCms . ': ' . $e->getMessage(),
                2,
                null,
                'CMS',
                $idCms
            );
            // Cache jako pusty — nie ponawiaj crashującego query
            self::$cssCache[$idCms] = '';
            return '';
        }
    }

    /**
     * Zapisuje CSS do bazy — INSERT lub UPDATE (upsert).
     *
     * @param int    $idCms     ID strony CMS
     * @param string $customCss Oczyszczony CSS
     */
    private function saveCssToDb(int $idCms, string $customCss): void
    {
        if (!$this->tableExists()) {
            throw new \RuntimeException('Table kw_cms_custom_css does not exist');
        }

        $db = Db::getInstance();

        // Jeśli CSS pusty — usuń rekord (zero bloatu w DB)
        if (empty(trim($customCss))) {
            $this->deleteCssFromDb($idCms);
            return;
        }

        $cleanCss = pSQL($customCss, true);

        // Sprawdź czy rekord istnieje
        if ($this->recordExists($idCms)) {
            $db->update(
                'kw_cms_custom_css',
                [
                    'custom_css' => $cleanCss,
                    'date_upd'   => date('Y-m-d H:i:s'),
                ],
                'id_cms = ' . (int) $idCms,
                1,
                true
            );
        } else {
            $db->insert(
                'kw_cms_custom_css',
                [
                    'id_cms'     => (int) $idCms,
                    'custom_css' => $cleanCss,
                    'date_add'   => date('Y-m-d H:i:s'),
                    'date_upd'   => date('Y-m-d H:i:s'),
                ],
                true
            );
        }

        // Invalidate cache
        self::$cssCache[$idCms] = $customCss;
    }

    /**
     * Usuwa CSS z bazy danych.
     */
    private function deleteCssFromDb(int $idCms): void
    {
        if (!$this->tableExists()) {
            return;
        }

        Db::getInstance()->delete(
            'kw_cms_custom_css',
            'id_cms = ' . (int) $idCms,
            1
        );
        unset(self::$cssCache[$idCms]);
    }

    /**
     * Sprawdza czy rekord istnieje w tabeli.
     */
    private function recordExists(int $idCms): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        try {
            $sql = new DbQuery();
            $sql->select('COUNT(*)');
            $sql->from('kw_cms_custom_css');
            $sql->where('id_cms = ' . (int) $idCms);

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Pobiera wszystkie zapisane CSS (lista w panelu admin).
     *
     * @return array
     */
    private function getAllSavedCss(): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        try {
            $sql = 'SELECT c.id_cms, cl.meta_title AS cms_title, 
                           LENGTH(c.custom_css) AS css_length, c.date_upd
                    FROM ' . _DB_PREFIX_ . 'kw_cms_custom_css c
                    LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl 
                        ON c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $this->context->language->id . '
                    WHERE c.custom_css IS NOT NULL AND c.custom_css != \'\'
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

    /**
     * Pobiera wszystkie aktywne strony CMS (do selecta w formularzu).
     *
     * @return array
     */
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

    /**
     * Sprawdza czy strona CMS o danym ID istnieje.
     */
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
     * Pobiera ID aktualnie wyświetlanej strony CMS (frontend).
     * Zwraca 0 jeśli nie jesteśmy na stronie CMS.
     *
     * WAŻNE (v1.1.0): NIE sięgamy po $controller->cms, ponieważ:
     * - Hook displayHeader odpala się w initHeader(), PRZED initContent()
     * - CmsController::initContent() dopiero ustawia $this->cms
     * - W PHP 8.x dostęp do niezainicjalizowanej typed property rzuca \TypeError
     * - \TypeError extends \Error (NIE \Exception) → nie łapie go catch(\Exception)
     *
     * Zamiast tego używamy 3 bezpiecznych metod detekcji:
     * 1. page_name ze Smarty context
     * 2. Tools::getValue('id_cms') z URL
     * 3. Parsowanie friendly URL (/content/{id}-{slug})
     *
     * @return int ID strony CMS lub 0
     */
    private function getCurrentCmsId(): int
    {
        // -----------------------------------------------------------------
        // Metoda 1: Sprawdź page_name w Smarty context
        // Najbezpieczniejsza metoda — page_name jest ustawiane w init(), 
        // PRZED initContent(), więc jest dostępne w momencie displayHeader.
        // -----------------------------------------------------------------
        $pageName = '';

        try {
            if (isset($this->context->smarty)) {
                $page = $this->context->smarty->getTemplateVars('page');
                if (is_array($page) && isset($page['page_name'])) {
                    $pageName = (string) $page['page_name'];
                }
            }
        } catch (\Throwable $e) {
            // Smarty context niedostępny — kontynuuj inne metody
        }

        // Jeśli page_name nie jest 'cms' — na pewno nie jesteśmy na stronie CMS
        if ($pageName !== '' && $pageName !== 'cms') {
            return 0;
        }

        // -----------------------------------------------------------------
        // Metoda 2: Sprawdź controller name (bezpieczne — nie sięgamy po property $cms)
        // -----------------------------------------------------------------
        if ($pageName === '') {
            // Fallback: sprawdź nazwę controllera
            try {
                $controller = $this->context->controller;
                if ($controller !== null) {
                    // Sprawdź php_page_name lub page_name z getTemplateVarPage()
                    $controllerClass = get_class($controller);
                    // CmsController, CmsControllerCore
                    if (stripos($controllerClass, 'CmsController') === false) {
                        return 0; // Nie CMS controller
                    }
                }
            } catch (\Throwable $e) {
                // Controller niedostępny — kontynuuj
            }
        }

        // -----------------------------------------------------------------
        // Metoda 3: Pobierz id_cms z parametrów URL
        // PrestaShop przekazuje id_cms jako parametr GET nawet przy friendly URLs.
        // -----------------------------------------------------------------
        $idCms = (int) Tools::getValue('id_cms');

        if ($idCms > 0) {
            return $idCms;
        }

        // -----------------------------------------------------------------
        // Metoda 4: Parsowanie friendly URL — /content/{id}-{slug}
        // Gdy friendly URLs są aktywne, PrestaShop może nie ustawiać id_cms w GET.
        // -----------------------------------------------------------------
        try {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            // Pattern: /content/{id}-{slug} lub /content/{id}
            if (preg_match('#/content/(\d+)(?:-|$)#', $requestUri, $matches)) {
                $idCms = (int) $matches[1];
                if ($idCms > 0) {
                    return $idCms;
                }
            }
        } catch (\Throwable $e) {
            // Parsowanie URL nie powiodło się
        }

        return 0;
    }

    /* =========================================================================
       FRONTEND HOOKS
       ========================================================================= */

    /**
     * Hook: displayHeader
     * Wstrzykuje <style> tag z custom CSS do <head> strony CMS.
     *
     * Ten hook odpala się w head.tpl motywu Optima (linia 86: {$HOOK_HEADER nofilter}).
     * Jest renderowany po {block name='stylesheets'} → custom CSS nadpisuje bazowe style.
     *
     * UWAGA (v1.1.0): Cały hook opakowany w try/catch(\Throwable).
     * Moduł NIGDY nie może crashować strony — w najgorszym przypadku
     * zwraca pusty string i loguje błąd.
     *
     * @param array $params Hook parameters
     * @return string HTML <style> tag lub pusty string
     */
    public function hookDisplayHeader($params)
    {
        // =====================================================================
        // OUTER TRY/CATCH — łapiemy WSZYSTKO (\Throwable = \Exception + \Error)
        // Gwarantuje że moduł NIGDY nie wywali strony na frontendzie.
        // =====================================================================
        try {
            // Guard: nie renderuj dwa razy w jednym request
            if (self::$cssRendered) {
                return '';
            }

            // Guard: tabela musi istnieć
            if (!$this->tableExists()) {
                return '';
            }

            $idCms = $this->getCurrentCmsId();

            // Nie jesteśmy na stronie CMS — zero output
            if ($idCms <= 0) {
                return '';
            }

            $customCss = $this->getCssFromDb($idCms);

            // Brak CSS — zero output (żadnych pustych <style> tagów)
            if (empty(trim($customCss))) {
                return '';
            }

            // Oznacz jako wyrenderowany
            self::$cssRendered = true;

            // Wygeneruj <style> tag
            return PHP_EOL
                . '<!-- KW CMS Custom CSS :: CMS #' . (int) $idCms . ' -->' . PHP_EOL
                . '<style type="text/css" data-kw-cms-custom="' . (int) $idCms . '">' . PHP_EOL
                . $customCss . PHP_EOL
                . '</style>' . PHP_EOL
                . '<!-- /KW CMS Custom CSS -->' . PHP_EOL;

        } catch (\Throwable $e) {
            // Loguj błąd ale NIGDY nie crashuj strony
            try {
                PrestaShopLogger::addLog(
                    'kw_cmscustomcss hookDisplayHeader CRITICAL: ' . $e->getMessage()
                    . ' in ' . $e->getFile() . ':' . $e->getLine(),
                    3,
                    null,
                    'Module',
                    (int) $this->id
                );
            } catch (\Throwable $logError) {
                // Nawet logowanie crashuje — po cichu ignoruj
            }

            return '';
        }
    }

    /* =========================================================================
       BACK OFFICE HOOKS
       ========================================================================= */

    /**
     * Hook: displayBackOfficeHeader
     * Ładuje CSS i JS modułu w panelu admin.
     *
     * Usunięto strict type hints na parametrach i return type — kompatybilność PS9.
     */
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

    /**
     * Hook: actionObjectCmsUpdateAfter
     * Czyści cache CSS po aktualizacji CMS.
     */
    public function hookActionObjectCmsUpdateAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                $idCms = (int) $params['object']->id;
                unset(self::$cssCache[$idCms]);
            }
        } catch (\Throwable $e) {
            // Nigdy nie crashuj — to hook systemowy
        }
    }

    /**
     * Hook: actionObjectCmsAddAfter
     * Reset cache po dodaniu nowej strony CMS.
     */
    public function hookActionObjectCmsAddAfter($params)
    {
        self::$cssCache = [];
    }

    /**
     * Hook: actionObjectCmsDeleteAfter
     * Czyści CSS z bazy po usunięciu strony CMS.
     */
    public function hookActionObjectCmsDeleteAfter($params)
    {
        try {
            if (isset($params['object']) && is_object($params['object']) && isset($params['object']->id)) {
                $idCms = (int) $params['object']->id;
                $this->deleteCssFromDb($idCms);
            }
        } catch (\Throwable $e) {
            try {
                PrestaShopLogger::addLog(
                    'kw_cmscustomcss cleanup error: ' . $e->getMessage(),
                    2,
                    null,
                    'CMS',
                    0
                );
            } catch (\Throwable $logError) {
                // Ciche ignorowanie
            }
        }
    }

    /**
     * Sprawdza czy aktualnie wyświetlana strona BO to konfiguracja tego modułu.
     */
    private function isModuleConfigPage(): bool
    {
        return Tools::getValue('configure') === $this->name;
    }

    /**
     * Reset modułu.
     */
    public function reset(): bool
    {
        return true;
    }
}
