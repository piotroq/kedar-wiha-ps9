<?php
/**
 * Plugin Name: Privacy Policy GDPR Manager
 * Plugin URI: https://github.com/piotroq/privacy-policy-gdpr-manager
 * Description: Kompleksowe zarządzanie polityką prywatności i zgodnością z RODO/GDPR
 * Version: 1.0.0
 * Author: PIOTROQ
 * Author URI: https://piotroq.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: privacy-policy-gdpr-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Tested up to: 6.5
 *
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

declare(strict_types=1);

namespace PBMedia\PrivacyPolicyGDPRManager;

if (!defined('ABSPATH')) {
    exit;
}

define('PPGM_VERSION', '1.0.0');
define('PPGM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PPGM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PPGM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Autoloader dla klas pluginu
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'PBMedia\\PrivacyPolicyGDPRManager\\';
    $base_dir = PPGM_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Inicjalizacja pluginu
 */
function ppgm_init(): void
{
    $validator = new Validator();
    $settings = new Settings($validator);
    $assets = new Assets($settings);

    add_action('admin_menu', [$settings, 'register_admin_menu']);
    add_action('admin_init', [$settings, 'register_settings']);
    add_action('admin_enqueue_scripts', [$assets, 'enqueue_admin_assets']);
    add_action('wp_enqueue_scripts', [$assets, 'enqueue_frontend_assets']);
    add_filter('script_loader_tag', [$assets, 'add_module_type'], 10, 3);

    register_activation_hook(__FILE__, __NAMESPACE__ . '\\ppgm_activate');
    register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\ppgm_deactivate');
}

/**
 * Hook aktywacji
 */
function ppgm_activate(): void
{
    $default_options = [
        'ppgm_email_contact' => '',
        'ppgm_privacy_url' => '',
        'ppgm_version' => PPGM_VERSION,
    ];

    if (false === get_option('ppgm_options')) {
        add_option('ppgm_options', $default_options);
    }

    flush_rewrite_rules();
}

/**
 * Hook deaktywacji
 */
function ppgm_deactivate(): void
{
    flush_rewrite_rules();
}

add_action('plugins_loaded', __NAMESPACE__ . '\\ppgm_init');