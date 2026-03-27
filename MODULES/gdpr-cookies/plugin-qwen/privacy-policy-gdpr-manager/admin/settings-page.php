<?php
/**
 * UI strony ustawień
 *
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

declare(strict_types=1);

namespace PBMedia\PrivacyPolicyGDPRManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renderowanie zaawansowanych ustawień
 */
function render_advanced_settings(): void
{
    $settings = new Settings(new Validator());
    ?>
    <div class="ppgm-advanced-settings">
        <h2><?php _e('Zaawansowane', 'privacy-policy-gdpr-manager'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Wersja pluginu', 'privacy-policy-gdpr-manager'); ?></th>
                <td><code><?php echo esc_html(PPGM_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Ścieżka pluginu', 'privacy-policy-gdpr-manager'); ?></th>
                <td><code><?php echo esc_html(PPGM_PLUGIN_DIR); ?></code></td>
            </tr>
        </table>
    </div>
    <?php
}