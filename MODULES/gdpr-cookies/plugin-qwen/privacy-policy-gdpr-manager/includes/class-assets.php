<?php
/**
 * Rejestracja i enqueue assetów
 *
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

declare(strict_types=1);

namespace PBMedia\PrivacyPolicyGDPRManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Klasa assetów
 */
class Assets
{
    /**
     * Instancja settings
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Wersja assetów
     *
     * @var string
     */
    private string $version;

    /**
     * Konstruktor
     *
     * @param Settings $settings Instancja settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->version = PPGM_VERSION;
    }

    /**
     * Enqueue assetów frontendowych
     */
    public function enqueue_frontend_assets(): void
    {
        $asset_url = PPGM_PLUGIN_URL . 'assets/';

        wp_register_style(
            'site-compliance-styles',
            $asset_url . 'css/compliance-styles.css',
            [],
            $this->version,
            'all'
        );

        wp_register_style(
            'site-iframe-styles',
            $asset_url . 'css/iframe-styles.css',
            ['site-compliance-styles'],
            $this->version,
            'all'
        );

        wp_register_script(
            'site-compliance-core',
            $asset_url . 'js/compliance-core.js',
            [],
            $this->version,
            true
        );

        wp_register_script(
            'site-iframe-loader',
            $asset_url . 'js/iframe-loader.js',
            ['site-compliance-core'],
            $this->version,
            true
        );

        wp_enqueue_style('site-compliance-styles');
        wp_enqueue_style('site-iframe-styles');
        wp_enqueue_script('site-compliance-core');
        wp_enqueue_script('site-iframe-loader');

        $this->enqueue_config_script();
    }

    /**
     * Enqueue skryptu konfiguracyjnego
     */
    private function enqueue_config_script(): void
    {
        $asset_url = PPGM_PLUGIN_URL . 'assets/';

        wp_register_script(
            'site-policy-config',
            $asset_url . 'js/compliance-config.js',
            ['site-compliance-core', 'site-iframe-loader'],
            $this->version,
            true
        );

        $config_data = [
            'privacyUrl' => $this->settings->get_privacy_url(),
            'contactEmail' => $this->settings->get_contact_email(),
            'pluginsUrl' => $asset_url . 'js/',
        ];

        wp_localize_script('site-policy-config', 'ppgmConfig', $config_data);
        wp_enqueue_script('site-policy-config');
    }

    /**
     * Dodaj type="module" do skryptu konfiguracyjnego
     *
     * @param string $tag Tag HTML
     * @param string $handle Handle skryptu
     * @param string $src Źródło skryptu
     * @return string Zmodyfikowany tag
     */
    public function add_module_type(string $tag, string $handle, string $src): string
    {
        if ('site-policy-config' === $handle) {
            return '<script type="module" src="' . esc_url($src) . '"></script>';
        }

        return $tag;
    }

    /**
     * Enqueue assetów admina
     *
     * @param string $hook Hook strony admina
     */
    public function enqueue_admin_assets(string $hook): void
    {
        if ($hook !== 'settings_page_privacy-policy-gdpr-manager') {
            return;
        }

        wp_enqueue_style(
            'ppgm-admin-styles',
            PPGM_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'ppgm-admin-scripts',
            PPGM_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_localize_script('ppgm-admin-scripts', 'ppgmAdmin', [
            'nonce' => wp_create_nonce('ppgm_admin_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }
}