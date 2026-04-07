<?php
/**
 * Insert Code HTML to HEAD/FOOTER
 *
 * PrestaShop 9.x module for injecting custom HTML/JS/CSS into the
 * <head> section and just before </body> on every frontend page.
 *
 * Version 1.1.4 — LIMIT clause regression fix:
 *
 * v1.1.3 used Db::update() with $limit=1 parameter, which appended
 * "LIMIT 1" to the generated SQL. PrestaShop 9 wraps the legacy Db
 * class with PrestaShopBundle\Doctrine\DatabaseConnection (Symfony
 * Doctrine bridge). When the value contains newlines (any multi-line
 * HTML like multiple <link> tags), the resulting multi-line SQL
 * combined with trailing LIMIT 1 produces a syntax error in MariaDB:
 *
 *   "SQLSTATE[42000]: Syntax error or access violation: 1064 ...
 *    near 'LIMIT 1' at line 5"
 *
 * Root cause: id_configuration is a PRIMARY KEY column, so any WHERE
 * clause matching it returns at most 1 row. LIMIT 1 was redundant
 * defensive cruft that broke under the Doctrine wrapper.
 *
 * v1.1.4 reverts to the canonical PrestaShop legacy pattern:
 * Db::execute() with manually-built single-quoted SQL, NO LIMIT
 * clauses anywhere. Captures both MySQL error message and a SQL
 * preview (truncated) for diagnostic display.
 *
 * Version history:
 *  - v1.0.0 → initial release
 *  - v1.1.0 → WAF/ModSecurity workaround via base64 transport
 *  - v1.1.1 → Smarty template fix ({literal} wrap)
 *  - v1.1.2 → direct DB save/read + verify-after-save (regression: " delimiters)
 *  - v1.1.3 → Db helper switch (regression: LIMIT 1 with Doctrine wrapper)
 *  - v1.1.4 → drop LIMIT clauses entirely, Db::execute + single-quoted SQL
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2024-2026 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 * @version   1.1.4
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsertCodeHeadFooter extends Module
{
    /** @var string Configuration key for HEAD code (v1.1.0+) */
    public const CONFIG_HEAD = 'ICHF_HEAD_CODE';

    /** @var string Configuration key for FOOTER code (v1.1.0+) */
    public const CONFIG_FOOTER = 'ICHF_FOOTER_CODE';

    /** @var string Legacy v1.0.0 HEAD key (auto-migrated on install) */
    private const LEGACY_CONFIG_HEAD = 'INSERTCODE_HEAD';

    /** @var string Legacy v1.0.0 FOOTER key (auto-migrated on install) */
    private const LEGACY_CONFIG_FOOTER = 'INSERTCODE_FOOTER';

    /** @var bool Static guard to prevent duplicate HEAD output per request */
    private static bool $headRendered = false;

    /** @var bool Static guard to prevent duplicate FOOTER output per request */
    private static bool $footerRendered = false;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->name = 'insertcodeheadfooter';
        $this->tab = 'front_office_features';
        $this->version = '1.1.4';
        $this->author = 'KEDAR-WIHA.pl';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '9.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Insert Code HTML to HEAD/FOOTER');
        $this->description = $this->l('Inject custom HTML, JavaScript, or CSS code into the HEAD section and before the closing BODY tag on every frontend page. WAF-safe base64 transport with direct-DB persistence via Db::execute and canonical single-quoted SQL.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? All saved HEAD and FOOTER code will be permanently deleted.');
    }

    /**
     * Install: register hooks, initialise keys (only if absent), migrate legacy.
     *
     * Hook selection (Optima 3.3.0 theme.yml analysis):
     * - displayHeader → rendered as last block in _partials/head.tpl line 86
     * - displayBeforeBodyClosingTag → rendered before </body> in
     *   layout-both-columns.tpl line 161, shared with posfakeorder /
     *   poscookielaw / pospopup; outputs concatenated via return.
     *
     * @return bool
     */
    public function install(): bool
    {
        if (!parent::install()
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayBeforeBodyClosingTag')) {
            return false;
        }

        // Only initialise keys when absent so a reinstall does not wipe data
        if (!$this->configKeyExists(self::CONFIG_HEAD)) {
            $this->saveConfigDirect(self::CONFIG_HEAD, '');
        }
        if (!$this->configKeyExists(self::CONFIG_FOOTER)) {
            $this->saveConfigDirect(self::CONFIG_FOOTER, '');
        }

        $this->migrateLegacyConfig();

        return true;
    }

    /**
     * Uninstall: remove hooks and configuration values.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->deleteConfigDirect(self::CONFIG_HEAD);
        $this->deleteConfigDirect(self::CONFIG_FOOTER);
        $this->deleteConfigDirect(self::LEGACY_CONFIG_HEAD);
        $this->deleteConfigDirect(self::LEGACY_CONFIG_FOOTER);

        return true;
    }

    /**
     * Migrate legacy v1.0.0 configuration keys to v1.1.0+ names.
     *
     * @return void
     */
    private function migrateLegacyConfig(): void
    {
        try {
            $legacyHead = $this->getConfigDirect(self::LEGACY_CONFIG_HEAD);
            if ($legacyHead !== '') {
                $this->saveConfigDirect(self::CONFIG_HEAD, $legacyHead);
                $this->deleteConfigDirect(self::LEGACY_CONFIG_HEAD);
            }
            $legacyFooter = $this->getConfigDirect(self::LEGACY_CONFIG_FOOTER);
            if ($legacyFooter !== '') {
                $this->saveConfigDirect(self::CONFIG_FOOTER, $legacyFooter);
                $this->deleteConfigDirect(self::LEGACY_CONFIG_FOOTER);
            }
        } catch (\Throwable $e) {
            $this->logError('migrateLegacyConfig', $e);
        }
    }

    /**
     * Display module configuration page in Back Office.
     *
     * @return string HTML output
     */
    public function getContent(): string
    {
        $output = '';
        $diagnostics = [];

        if (Tools::isSubmit('ichf_save')) {
            $result = $this->handleSaveSubmission();
            $diagnostics = $result['diagnostics'];

            if ($result['success']) {
                $output .= $this->displayConfirmation($result['message']);
            } else {
                $output .= $this->displayError($result['message']);
            }
        }

        if (Tools::isSubmit('ichf_clear_cache')) {
            if ($this->validateAdminToken()) {
                $this->clearAllCaches();
                $output .= $this->displayConfirmation(
                    $this->l('Cache cleared successfully.')
                );
            } else {
                $output .= $this->displayError(
                    $this->l('Invalid security token. Please reload the page and try again.')
                );
            }
        }

        return $output . $this->renderConfigForm($diagnostics);
    }

    /**
     * Process the "Save" POST submission.
     *
     * @return array{success:bool,message:string,diagnostics:array<string,mixed>}
     */
    private function handleSaveSubmission(): array
    {
        $diagnostics = [
            'transport_head' => 'none',
            'transport_footer' => 'none',
            'head_input_bytes' => 0,
            'footer_input_bytes' => 0,
            'head_db_bytes' => 0,
            'footer_db_bytes' => 0,
            'head_save_ok' => false,
            'footer_save_ok' => false,
            'head_save_method' => '',
            'footer_save_method' => '',
            'head_save_error' => '',
            'footer_save_error' => '',
            'head_sql_preview' => '',
            'footer_sql_preview' => '',
            'post_keys' => array_keys($_POST),
        ];

        if (!$this->validateAdminToken()) {
            return [
                'success' => false,
                'message' => $this->l('Invalid security token. Please reload the page and try again.'),
                'diagnostics' => $diagnostics,
            ];
        }

        // Resolve HEAD value (base64 preferred, raw fallback)
        $headResult = $this->resolvePostValue('ichf_h_b64', 'ichf_h_value');
        $headCode = $headResult['value'];
        $diagnostics['transport_head'] = $headResult['transport'];
        $diagnostics['head_input_bytes'] = strlen($headCode);

        // Resolve FOOTER value (base64 preferred, raw fallback)
        $footerResult = $this->resolvePostValue('ichf_f_b64', 'ichf_f_value');
        $footerCode = $footerResult['value'];
        $diagnostics['transport_footer'] = $footerResult['transport'];
        $diagnostics['footer_input_bytes'] = strlen($footerCode);

        // Persist via Db::execute with canonical single-quoted SQL (NO LIMIT)
        $headSave = $this->saveConfigDirect(self::CONFIG_HEAD, $headCode);
        $diagnostics['head_save_ok'] = $headSave['ok'];
        $diagnostics['head_save_method'] = $headSave['method'];
        $diagnostics['head_save_error'] = $headSave['error'];
        $diagnostics['head_sql_preview'] = $headSave['sql_preview'];

        $footerSave = $this->saveConfigDirect(self::CONFIG_FOOTER, $footerCode);
        $diagnostics['footer_save_ok'] = $footerSave['ok'];
        $diagnostics['footer_save_method'] = $footerSave['method'];
        $diagnostics['footer_save_error'] = $footerSave['error'];
        $diagnostics['footer_sql_preview'] = $footerSave['sql_preview'];

        // Verify-after-save: read fresh from DB and count actual stored bytes
        $diagnostics['head_db_bytes'] = strlen($this->getConfigDirect(self::CONFIG_HEAD));
        $diagnostics['footer_db_bytes'] = strlen($this->getConfigDirect(self::CONFIG_FOOTER));

        // Detect persistence mismatch: stored bytes must equal input bytes
        $headPersistOk = ($headSave['ok']
            && $diagnostics['head_db_bytes'] === $diagnostics['head_input_bytes']);
        $footerPersistOk = ($footerSave['ok']
            && $diagnostics['footer_db_bytes'] === $diagnostics['footer_input_bytes']);

        if (!$headPersistOk || !$footerPersistOk) {
            return [
                'success' => false,
                'message' => $this->l('Save partially failed: stored bytes do not match input bytes. See diagnostics below.'),
                'diagnostics' => $diagnostics,
            ];
        }

        $this->clearAllCaches();

        return [
            'success' => true,
            'message' => $this->l('Settings saved successfully. Smarty cache has been cleared.'),
            'diagnostics' => $diagnostics,
        ];
    }

    /**
     * Resolve a POST value from either base64 or raw field.
     *
     * @param string $b64Key Base64-encoded POST field name
     * @param string $rawKey Raw POST field name (NoScript fallback)
     * @return array{value:string,transport:string}
     */
    private function resolvePostValue(string $b64Key, string $rawKey): array
    {
        // Path 1: base64-encoded (preferred, WAF-safe)
        if (isset($_POST[$b64Key]) && is_string($_POST[$b64Key]) && $_POST[$b64Key] !== '') {
            $encoded = trim((string) $_POST[$b64Key]);

            if (preg_match('#^[A-Za-z0-9+/=]+$#', $encoded) === 1) {
                $decoded = base64_decode($encoded, true);
                if ($decoded !== false) {
                    return ['value' => $decoded, 'transport' => 'base64'];
                }
            }
        }

        // Path 2: raw POST field (NoScript fallback)
        if (isset($_POST[$rawKey]) && is_string($_POST[$rawKey])) {
            return ['value' => (string) $_POST[$rawKey], 'transport' => 'raw'];
        }

        return ['value' => '', 'transport' => 'empty'];
    }

    /**
     * Save a configuration value via Db::execute with canonical
     * single-quoted SQL.
     *
     * NO LIMIT clauses are used anywhere — id_configuration is a
     * PRIMARY KEY so the WHERE clause matches at most 1 row by
     * definition. LIMIT 1 was the v1.1.3 regression cause: PS 9
     * Doctrine wrapper combined with multi-line value content
     * produced a syntax error near "LIMIT 1".
     *
     * Captures Db::getMsgError() and a SQL preview (truncated) on
     * failure for diagnostic display.
     *
     * @param string $key   Configuration name
     * @param string $value Raw value to store
     * @return array{ok:bool,error:string,method:string,sql_preview:string}
     */
    private function saveConfigDirect(string $key, string $value): array
    {
        $result = ['ok' => false, 'error' => '', 'method' => '', 'sql_preview' => ''];

        try {
            $db = Db::getInstance();
            $tableName = _DB_PREFIX_ . 'configuration';
            $escapedKey = pSQL($key);
            $escapedValue = pSQL($value, true); // htmlOK=true preserves HTML
            $now = date('Y-m-d H:i:s');

            // Find existing global (non-shop-specific) row.
            // NO LIMIT — name is functionally unique with the shop conditions.
            $existingId = (int) $db->getValue(
                'SELECT `id_configuration` FROM `' . $tableName . '`
                 WHERE `name` = \'' . $escapedKey . '\'
                 AND (`id_shop` IS NULL OR `id_shop` = 0)
                 AND (`id_shop_group` IS NULL OR `id_shop_group` = 0)'
            );

            if ($existingId > 0) {
                $result['method'] = 'UPDATE id=' . $existingId;
                // Canonical PS legacy pattern: single-quoted SQL string,
                // pSQL-escaped values, NO LIMIT (PK is unique).
                $sql = 'UPDATE `' . $tableName . '` SET '
                     . '`value` = \'' . $escapedValue . '\', '
                     . '`date_upd` = \'' . $now . '\' '
                     . 'WHERE `id_configuration` = ' . $existingId;
            } else {
                $result['method'] = 'INSERT new row';
                $sql = 'INSERT INTO `' . $tableName . '` '
                     . '(`name`, `value`, `date_add`, `date_upd`) '
                     . 'VALUES ('
                     . '\'' . $escapedKey . '\', '
                     . '\'' . $escapedValue . '\', '
                     . '\'' . $now . '\', '
                     . '\'' . $now . '\')';
            }

            // Store truncated SQL preview for diagnostic display (max 300 chars)
            $result['sql_preview'] = mb_substr($sql, 0, 300);

            $ok = $db->execute($sql);
            $result['ok'] = (bool) $ok;

            if (!$ok) {
                $result['error'] = (string) $db->getMsgError();
                $this->logError(
                    'saveConfigDirect:' . $key . ':' . $result['method'],
                    new \RuntimeException(
                        'Db::execute failed. MySQL: ' . $result['error']
                        . ' | SQL preview: ' . $result['sql_preview']
                    )
                );
            }

            // Best-effort cache invalidation
            $this->invalidateConfigurationCache($key, $value);

            return $result;
        } catch (\Throwable $e) {
            $this->logError('saveConfigDirect:' . $key, $e);
            $result['error'] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Read a configuration value via direct SQL.
     *
     * NO LIMIT — name is functionally unique with the shop conditions.
     *
     * @param string $key Configuration name
     * @return string Raw value or empty string
     */
    private function getConfigDirect(string $key): string
    {
        try {
            $db = Db::getInstance();

            $value = $db->getValue(
                'SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration`
                 WHERE `name` = \'' . pSQL($key) . '\'
                 AND (`id_shop` IS NULL OR `id_shop` = 0)
                 AND (`id_shop_group` IS NULL OR `id_shop_group` = 0)'
            );

            return is_string($value) ? $value : '';
        } catch (\Throwable $e) {
            $this->logError('getConfigDirect:' . $key, $e);
            return '';
        }
    }

    /**
     * Delete configuration rows for a key via Db::execute.
     *
     * @param string $key Configuration name
     * @return bool
     */
    private function deleteConfigDirect(string $key): bool
    {
        try {
            $db = Db::getInstance();

            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'configuration` '
                 . 'WHERE `name` = \'' . pSQL($key) . '\'';

            $ok = $db->execute($sql);

            $this->invalidateConfigurationCache($key, false);

            return (bool) $ok;
        } catch (\Throwable $e) {
            $this->logError('deleteConfigDirect:' . $key, $e);
            return false;
        }
    }

    /**
     * Check whether a configuration key already exists in DB.
     *
     * @param string $key Configuration name
     * @return bool
     */
    private function configKeyExists(string $key): bool
    {
        try {
            $db = Db::getInstance();

            $count = (int) $db->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'configuration`
                 WHERE `name` = \'' . pSQL($key) . '\'
                 AND (`id_shop` IS NULL OR `id_shop` = 0)
                 AND (`id_shop_group` IS NULL OR `id_shop_group` = 0)'
            );

            return $count > 0;
        } catch (\Throwable $e) {
            $this->logError('configKeyExists:' . $key, $e);
            return false;
        }
    }

    /**
     * Best-effort invalidation of PS Configuration in-memory cache.
     *
     * @param string       $key
     * @param string|false $value New value, or false to drop from cache
     * @return void
     */
    private function invalidateConfigurationCache(string $key, $value = ''): void
    {
        try {
            if (method_exists('Configuration', 'set')) {
                Configuration::set($key, $value === false ? null : (string) $value);
            }
        } catch (\Throwable $e) {
            // Cache invalidation is best-effort
        }
    }

    /**
     * Hook: displayHeader
     *
     * Injects custom code into the <head> section.
     *
     * @param array $params Hook parameters (unused)
     * @return string Rendered HTML or empty string
     */
    public function hookDisplayHeader(array $params): string
    {
        if (self::$headRendered) {
            return '';
        }
        self::$headRendered = true;

        try {
            $headCode = $this->getConfigDirect(self::CONFIG_HEAD);

            if ($headCode === '') {
                return '';
            }

            $this->context->smarty->assign([
                'ichf_head' => $headCode,
            ]);

            return $this->display(__FILE__, 'views/templates/hook/head_code.tpl');
        } catch (\Throwable $e) {
            $this->logError('hookDisplayHeader', $e);
            return '';
        }
    }

    /**
     * Hook: displayBeforeBodyClosingTag
     *
     * Injects custom code just before </body>.
     *
     * @param array $params Hook parameters (unused)
     * @return string Rendered HTML or empty string
     */
    public function hookDisplayBeforeBodyClosingTag(array $params): string
    {
        if (self::$footerRendered) {
            return '';
        }
        self::$footerRendered = true;

        try {
            $footerCode = $this->getConfigDirect(self::CONFIG_FOOTER);

            if ($footerCode === '') {
                return '';
            }

            $this->context->smarty->assign([
                'ichf_footer' => $footerCode,
            ]);

            return $this->display(__FILE__, 'views/templates/hook/footer_code.tpl');
        } catch (\Throwable $e) {
            $this->logError('hookDisplayBeforeBodyClosingTag', $e);
            return '';
        }
    }

    /**
     * Render the configuration form with current values and diagnostics.
     *
     * @param array<string,mixed> $diagnostics Debug info from last save
     * @return string Rendered form HTML
     */
    private function renderConfigForm(array $diagnostics = []): string
    {
        $adminToken = $this->getAdminToken();

        // Read fresh from DB (bypass Configuration cache)
        $headValue = $this->getConfigDirect(self::CONFIG_HEAD);
        $footerValue = $this->getConfigDirect(self::CONFIG_FOOTER);

        $this->context->smarty->assign([
            'ichf_head_value' => $headValue,
            'ichf_footer_value' => $footerValue,
            'ichf_head_value_b64' => base64_encode($headValue),
            'ichf_footer_value_b64' => base64_encode($footerValue),
            'ichf_action' => $this->getFormAction($adminToken),
            'ichf_token' => $adminToken,
            'ichf_module_name' => $this->displayName,
            'ichf_module_version' => $this->version,
            'ichf_diagnostics' => $diagnostics,
            'ichf_has_diagnostics' => !empty($diagnostics),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Build the admin form action URL compatible with PS9 routing.
     *
     * @param string $adminToken
     * @return string
     */
    private function getFormAction(string $adminToken): string
    {
        return $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name
            . '&token=' . $adminToken;
    }

    /**
     * Validate the admin security token.
     *
     * @return bool
     */
    private function validateAdminToken(): bool
    {
        $expectedToken = $this->getAdminToken();

        // Strategy 1: dedicated POST hidden field
        $postToken = isset($_POST['ichf_admin_token']) ? (string) $_POST['ichf_admin_token'] : '';
        if ($postToken !== '' && hash_equals($expectedToken, $postToken)) {
            return true;
        }

        // Strategy 2: legacy token parameter
        $legacyToken = Tools::getValue('token', '');
        if (is_string($legacyToken) && $legacyToken !== '' && hash_equals($expectedToken, $legacyToken)) {
            return true;
        }

        // Strategy 3: authenticated employee session fallback
        if (isset($this->context->employee) && (int) $this->context->employee->id > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get the admin security token for AdminModules controller.
     *
     * @return string
     */
    private function getAdminToken(): string
    {
        return Tools::getAdminTokenLite('AdminModules');
    }

    /**
     * Clear all relevant caches after configuration save.
     *
     * @return void
     */
    private function clearAllCaches(): void
    {
        try {
            if (method_exists('Tools', 'clearSmartyCache')) {
                Tools::clearSmartyCache();
            }
            if (method_exists('Tools', 'clearXMLCache')) {
                Tools::clearXMLCache();
            }
            if (method_exists('Tools', 'clearCache')) {
                Tools::clearCache();
            }
        } catch (\Throwable $e) {
            $this->logError('clearAllCaches', $e);
        }
    }

    /**
     * Log an error to the PrestaShop logger.
     *
     * @param string     $context Error context description
     * @param \Throwable $e       Caught exception/error
     * @return void
     */
    private function logError(string $context, \Throwable $e): void
    {
        try {
            PrestaShopLogger::addLog(
                sprintf(
                    '[%s] %s: %s in %s:%d',
                    $this->name,
                    $context,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                3,
                $e->getCode(),
                null,
                null,
                true
            );
        } catch (\Throwable $innerException) {
            // Logging must never crash the module
        }
    }
}
