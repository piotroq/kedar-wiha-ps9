<?php
/**
 * KEDAR-WIHA — Custom Block (HTML/CSS/JS per hook)
 * ════════════════════════════════════════════════════════════
 * Plik  : modules/kedarwiha_customblock/kedarwiha_customblock.php
 *
 * Pozwala dodawać własne bloki HTML/CSS/JS na dowolny hook PS9.
 * Konfiguracja: Admin → Moduły → KEDAR-WIHA Custom Block → Konfiguruj
 *
 * Każdy blok ma:
 *   - hook (np. displayHome, displayFooter, displayCMSPageContent)
 *   - tytuł (tylko admin)
 *   - zawartość HTML (WYSIWYG lub raw)
 *   - CSS (inline <style>)
 *   - JS (inline <script>)
 *   - status aktywny/nieaktywny
 *   - id_cms (opcjonalnie — tylko na konkretnej stronie CMS)
 * ════════════════════════════════════════════════════════════
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kedarwiha_Customblock extends Module
{
    public function __construct()
    {
        $this->name          = 'kedarwiha_customblock';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'KEDAR-WIHA Dev';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = 'KEDAR-WIHA Custom Block';
        $this->description = 'Dodawaj bloki HTML/CSS/JS na dowolny hook PS9.';
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        return parent::install()
            && $this->installDB()
            && $this->registerHooks();
    }

    public function uninstall(): bool
    {
        return parent::uninstall();
        // DB pozostaje — dane są cenne
    }

    private function installDB(): bool
    {
        $p = _DB_PREFIX_;
        return Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$p}kw_custom_blocks` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title`      VARCHAR(255) NOT NULL,
                `hook_name`  VARCHAR(100) NOT NULL,
                `content`    MEDIUMTEXT   DEFAULT NULL,
                `custom_css` TEXT         DEFAULT NULL,
                `custom_js`  TEXT         DEFAULT NULL,
                `id_cms`     INT UNSIGNED DEFAULT NULL COMMENT 'Jeśli podane — blok tylko na tej stronie CMS',
                `active`     TINYINT(1)   NOT NULL DEFAULT 1,
                `sort_order` SMALLINT     NOT NULL DEFAULT 0,
                `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_kcb_hook`   (`hook_name`),
                KEY `idx_kcb_active` (`active`),
                KEY `idx_kcb_cms`    (`id_cms`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Rejestruje hooki które mogą być używane przez bloki.
     * Rozszerz tę listę według potrzeb.
     */
    private function registerHooks(): bool
    {
        $hooks = [
            'displayHome',
            'displayTop',
            'displayBanner',
            'displayFooter',
            'displayFooterBefore',
            'displayCMSPageContent',
            'displayCMSPageFooter',
            'displayProductAdditionalInfo',
            'displayShoppingCartFooter',
            'displayOrderConfirmation',
            'displayCustomerAccount',
            'displayLeftColumn',
            'displayRightColumn',
            'displayContentWrapperTop',
            'displayWrapperTop',
            'displayWrapperBottom',
            'displayFooter',
            'displayFooterBefore',
            'displayFooterProduct',
            'displayFooterBuilder',
            'displayHeaderBuilder',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                // Nie przerywaj instalacji jeśli hook nie istnieje w PS9
                continue;
            }
        }

        return true;
    }

    /* ── HOOK DISPATCHER ── */

    /**
     * Magiczna metoda — obsługuje KAŻDY zarejestrowany hook.
     * PS9 wywołuje hookDisplayHome → __call('hookDisplayHome', $params)
     *
     * @param string $name   Nazwa metody (np. hookDisplayHome)
     * @param array  $args   Parametry hooka
     */
    public function __call(string $name, array $args): string
    {
        // Wyciągnij nazwę hooka z prefiksu "hook"
        if (!str_starts_with($name, 'hook')) {
            return '';
        }

        $hookName = lcfirst(substr($name, 4)); // hookDisplayHome → displayHome
        $params   = $args[0] ?? [];

        return $this->renderBlocks($hookName, $params);
    }

    /**
     * Pobiera i renderuje aktywne bloki dla danego hooka.
     */
    private function renderBlocks(string $hookName, array $params): string
    {
        $idCms  = (int) ($this->context->controller->id_cms ?? 0);
        $p      = _DB_PREFIX_;

        // Pobierz bloki dla tego hooka (z uwzględnieniem id_cms jeśli podane)
        $blocks = Db::getInstance()->executeS(
            "SELECT `id`, `content`, `custom_css`, `custom_js`
             FROM `{$p}kw_custom_blocks`
             WHERE `hook_name` = '" . pSQL($hookName) . "'
               AND `active` = 1
               AND (`id_cms` IS NULL OR `id_cms` = " . $idCms . ")
             ORDER BY `sort_order` ASC, `id` ASC"
        );

        if (empty($blocks)) {
            return '';
        }

        $output = '';
        foreach ($blocks as $block) {
            $output .= $this->renderBlock($block);
        }

        return $output;
    }

    private function renderBlock(array $block): string
    {
        $out = '';

        // CSS
        if (!empty(trim($block['custom_css']))) {
            $out .= '<style>' . $block['custom_css'] . '</style>';
        }

        // HTML
        if (!empty(trim($block['content']))) {
            $out .= '<div class="kw-custom-block" data-block-id="' . (int) $block['id'] . '">'
                 . $block['content']
                 . '</div>';
        }

        // JS
        if (!empty(trim($block['custom_js']))) {
            $out .= '<script>' . $block['custom_js'] . '</script>';
        }

        return $out;
    }

    /* ── CONFIG PAGE ── */

    public function getContent(): string
    {
        $output = '';

        // Zapis nowego/edytowanego bloku
        if (Tools::isSubmit('submitKwBlock')) {
            $output .= $this->saveBlock();
        }

        // Usunięcie bloku
        if (Tools::getValue('delete_block')) {
            $output .= $this->deleteBlock((int) Tools::getValue('delete_block'));
        }

        // Toggle aktywności
        if (Tools::getValue('toggle_block')) {
            $this->toggleBlock((int) Tools::getValue('toggle_block'));
        }

        $output .= $this->renderAdminForm();
        $output .= $this->renderBlockList();

        return $output;
    }

    private function saveBlock(): string
    {
        $id      = (int) Tools::getValue('block_id');
        $title   = pSQL(Tools::getValue('block_title', ''));
        $hook    = pSQL(Tools::getValue('block_hook', ''));
        $content = Tools::getValue('block_content', '');
        $css     = Tools::getValue('block_css', '');
        $js      = Tools::getValue('block_js', '');
        $idCms   = (int) Tools::getValue('block_id_cms') ?: null;
        $sort    = (int) Tools::getValue('block_sort', 0);

        if (empty($title) || empty($hook)) {
            return $this->displayError('Tytuł i hook są wymagane.');
        }

        $data = [
            'title'      => $title,
            'hook_name'  => $hook,
            'content'    => $content,
            'custom_css' => $css,
            'custom_js'  => $js,
            'id_cms'     => $idCms,
            'sort_order' => $sort,
            'active'     => 1,
        ];

        if ($id) {
            Db::getInstance()->update('kw_custom_blocks', $data, '`id` = ' . $id);
        } else {
            Db::getInstance()->insert('kw_custom_blocks', $data);
        }

        return $this->displayConfirmation('Blok zapisany.');
    }

    private function deleteBlock(int $id): string
    {
        if ($id > 0) {
            Db::getInstance()->delete('kw_custom_blocks', '`id` = ' . $id);
            return $this->displayConfirmation('Blok usunięty.');
        }
        return '';
    }

    private function toggleBlock(int $id): void
    {
        if ($id > 0) {
            $current = (int) Db::getInstance()->getValue(
                'SELECT `active` FROM `' . _DB_PREFIX_ . 'kw_custom_blocks` WHERE `id` = ' . $id
            );
            Db::getInstance()->update('kw_custom_blocks', ['active' => $current ? 0 : 1], '`id` = ' . $id);
        }
    }

    private function renderAdminForm(): string
    {
        $hooks = [
            'displayHome', 'displayTop', 'displayBanner', 'displayFooter',
            'displayFooterBefore', 'displayCMSPageContent', 'displayCMSPageFooter',
            'displayProductAdditionalInfo', 'displayShoppingCartFooter',
            'displayOrderConfirmation', 'displayLeftColumn', 'displayRightColumn',
            'displayContentWrapperTop', 'displayWrapperTop', 'displayWrapperBottom',
            'displayFooter', 'displayFooterBefore', 'displayFooterProduct', 'displayFooterBuilder',
            'displayHeaderBuilder',
        ];

        $hookOptions = '';
        foreach ($hooks as $h) {
            $hookOptions .= '<option value="' . $h . '">' . $h . '</option>';
        }

        return '
        <div class="panel">
          <div class="panel-heading">
            <i class="icon-plus-sign"></i> Dodaj nowy blok
          </div>
          <div class="panel-body">
            <form method="post" action="' . $this->context->link->getAdminLink('AdminModules', true, ['configure' => $this->name]) . '">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tytuł bloku *</label>
                    <input type="text" name="block_title" class="form-control" placeholder="np. Baner promocyjny strona główna">
                  </div>
                  <div class="form-group">
                    <label>Hook *</label>
                    <select name="block_hook" class="form-control">' . $hookOptions . '</select>
                  </div>
                  <div class="form-group">
                    <label>ID strony CMS (opcjonalne — puste = wszystkie)</label>
                    <input type="number" name="block_id_cms" class="form-control" placeholder="np. 8 dla strony Kontakt">
                  </div>
                  <div class="form-group">
                    <label>Kolejność (sort order)</label>
                    <input type="number" name="block_sort" class="form-control" value="0">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Zawartość HTML</label>
                    <textarea name="block_content" class="form-control" rows="6" placeholder="<div>Twój HTML...</div>"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Custom CSS (bez tagów &lt;style&gt;)</label>
                    <textarea name="block_css" class="form-control" rows="3" placeholder=".moja-klasa { color: red; }"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Custom JS (bez tagów &lt;script&gt;)</label>
                    <textarea name="block_js" class="form-control" rows="3" placeholder="document.addEventListener(\'DOMContentLoaded\', function() { ... });"></textarea>
                  </div>
                </div>
              </div>
              <input type="hidden" name="block_id" value="0">
              <button type="submit" name="submitKwBlock" class="btn btn-primary">
                <i class="icon-save"></i> Zapisz blok
              </button>
            </form>
          </div>
        </div>';
    }

    private function renderBlockList(): string
    {
        $blocks = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'kw_custom_blocks` ORDER BY `sort_order` ASC, `id` ASC'
        ) ?: [];

        if (empty($blocks)) {
            return '<div class="alert alert-info">Brak bloków. Dodaj pierwszy blok powyżej.</div>';
        }

        $adminUrl = $this->context->link->getAdminLink('AdminModules', true, ['configure' => $this->name]);
        $rows     = '';

        foreach ($blocks as $b) {
            $statusBadge = $b['active']
                ? '<span class="badge" style="background:#2E7D32">Aktywny</span>'
                : '<span class="badge bg-secondary">Nieaktywny</span>';

            $rows .= '<tr>
                <td>' . (int) $b['id'] . '</td>
                <td><strong>' . htmlspecialchars($b['title']) . '</strong></td>
                <td><code>' . htmlspecialchars($b['hook_name']) . '</code></td>
                <td>' . ($b['id_cms'] ? '#' . $b['id_cms'] : '<small class="text-muted">wszystkie</small>') . '</td>
                <td>' . $statusBadge . '</td>
                <td>' . (int) $b['sort_order'] . '</td>
                <td>
                  <a href="' . $adminUrl . '&toggle_block=' . $b['id'] . '" class="btn btn-xs btn-default">
                    ' . ($b['active'] ? 'Wyłącz' : 'Włącz') . '
                  </a>
                  <a href="' . $adminUrl . '&delete_block=' . $b['id'] . '"
                     class="btn btn-xs btn-danger"
                     onclick="return confirm(\'Usunąć blok?\')">Usuń</a>
                </td>
              </tr>';
        }

        return '
        <div class="panel">
          <div class="panel-heading">Lista bloków</div>
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th><th>Tytuł</th><th>Hook</th><th>CMS ID</th>
                  <th>Status</th><th>Sort</th><th>Akcje</th>
                </tr>
              </thead>
              <tbody>' . $rows . '</tbody>
            </table>
          </div>
        </div>';
    }
}
