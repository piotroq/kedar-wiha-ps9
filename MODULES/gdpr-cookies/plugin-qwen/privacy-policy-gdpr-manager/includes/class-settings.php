<?php
/**
 * Zarządzanie ustawieniami admina
 *
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

declare(strict_types=1);

namespace PBMedia\PrivacyPolicyGDPRManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Klasa ustawień
 */
class Settings
{
    /**
     * Instancja validatora
     *
     * @var Validator
     */
    private Validator $validator;

    /**
     * Opcje pluginu
     *
     * @var array<string,mixed>
     */
    private array $options;

    /**
     * Konstruktor
     *
     * @param Validator $validator Instancja validatora
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
        $this->options = get_option('ppgm_options', []);
    }

    /**
     * Rejestracja menu admina
     */
    public function register_admin_menu(): void
    {
        add_submenu_page(
            'options-general.php',
            __('Privacy Policy GDPR', 'privacy-policy-gdpr-manager'),
            __('Privacy Policy GDPR', 'privacy-policy-gdpr-manager'),
            'manage_options',
            'privacy-policy-gdpr-manager',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Rejestracja ustawień
     */
    public function register_settings(): void
    {
        register_setting(
            'ppgm_settings_group',
            'ppgm_options',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_options'],
                'default' => [
                    'ppgm_email_contact' => '',
                    'ppgm_privacy_url' => '',
                ],
            ]
        );

        add_settings_section(
            'ppgm_main_section',
            __('Ustawienia Polityki Prywatności', 'privacy-policy-gdpr-manager'),
            null,
            'privacy-policy-gdpr-manager'
        );

        add_settings_field(
            'ppgm_email_contact',
            __('Email kontaktowy', 'privacy-policy-gdpr-manager'),
            [$this, 'render_email_field'],
            'privacy-policy-gdpr-manager',
            'ppgm_main_section'
        );

        add_settings_field(
            'ppgm_privacy_url',
            __('URL Polityki Prywatności', 'privacy-policy-gdpr-manager'),
            [$this, 'render_url_field'],
            'privacy-policy-gdpr-manager',
            'ppgm_main_section'
        );
    }

    /**
     * Sanityzacja opcji
     *
     * @param array<string,mixed> $input Dane wejściowe
     * @return array<string,mixed> Wyczyszczone dane
     */
    public function sanitize_options(array $input): array
    {
        $sanitized = [
            'ppgm_email_contact' => $this->validator->sanitize_email($input['ppgm_email_contact'] ?? ''),
            'ppgm_privacy_url' => $this->validator->sanitize_url($input['ppgm_privacy_url'] ?? ''),
        ];

        $errors = $this->validator->validate_form($sanitized);

        if (!empty($errors)) {
            add_settings_error(
                'ppgm_options',
                'ppgm_validation_error',
                implode('<br>', $errors),
                'error'
            );

            return $this->options;
        }

        return $sanitized;
    }

    /**
     * Renderowanie pola email
     */
    public function render_email_field(): void
    {
        $value = esc_attr($this->options['ppgm_email_contact'] ?? '');
        $nonce = wp_create_nonce('ppgm_settings_nonce');

        echo '<input type="email" name="ppgm_options[ppgm_email_contact]" value="' . $value . '" class="regular-text" required>';
        echo '<input type="hidden" name="ppgm_nonce" value="' . $nonce . '">';
        echo '<p class="description">' . __('Adres email do kontaktu w sprawach polityki prywatności', 'privacy-policy-gdpr-manager') . '</p>';
    }

    /**
     * Renderowanie pola URL
     */
    public function render_url_field(): void
    {
        $value = esc_attr($this->options['ppgm_privacy_url'] ?? '');
        $default_url = get_the_privacy_policy_link();

        echo '<input type="url" name="ppgm_options[ppgm_privacy_url]" value="' . $value . '" class="regular-text" placeholder="' . esc_attr($default_url) . '">';
        echo '<p class="description">' . __('Pozostaw puste, aby użyć domyślnej strony polityki prywatności WordPress', 'privacy-policy-gdpr-manager') . '</p>';
    }

    /**
     * Renderowanie strony ustawień
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Brak uprawnień', 'privacy-policy-gdpr-manager'));
        }

        $nonce = $_POST['ppgm_nonce'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !wp_verify_nonce($nonce, 'ppgm_settings_nonce')) {
            wp_die(__('Błąd weryfikacji nonce', 'privacy-policy-gdpr-manager'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ppgm_settings_group');
                do_settings_sections('privacy-policy-gdpr-manager');
                submit_button(__('Zapisz ustawienia', 'privacy-policy-gdpr-manager'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Pobierz opcję
     *
     * @param string $key Klucz opcji
     * @param mixed $default Domyślna wartość
     * @return mixed Wartość opcji
     */
    public function get_option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Pobierz URL polityki prywatności
     *
     * @return string URL polityki prywatności
     */
    public function get_privacy_url(): string
    {
        $custom_url = $this->get_option('ppgm_privacy_url');

        if (!empty($custom_url)) {
            return $custom_url;
        }

        return get_the_privacy_policy_link() ?? '#';
    }

    /**
     * Pobierz email kontaktowy
     *
     * @return string Email kontaktowy
     */
    public function get_contact_email(): string
    {
        return $this->get_option('ppgm_email_contact', get_option('admin_email', ''));
    }
}