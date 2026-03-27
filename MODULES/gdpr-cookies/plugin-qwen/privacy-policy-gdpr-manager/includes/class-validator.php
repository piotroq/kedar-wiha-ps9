<?php
/**
 * Walidacja i sanityzacja danych
 *
 * @package PBMedia\PrivacyPolicyGDPRManager
 */

declare(strict_types=1);

namespace PBMedia\PrivacyPolicyGDPRManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Klasa validatora
 */
class Validator
{
    /**
     * Walidacja adresu email
     *
     * @param string $email Adres email do walidacji
     * @return bool True jeśli poprawny
     */
    public function validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Walidacja URL
     *
     * @param string $url URL do walidacji
     * @return bool True jeśli poprawny
     */
    public function validate_url(string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Sanityzacja email
     *
     * @param string $email Email do sanityzacji
     * @return string Wyczyszczony email
     */
    public function sanitize_email(string $email): string
    {
        return sanitize_email($email);
    }

    /**
     * Sanityzacja URL
     *
     * @param string $url URL do sanityzacji
     * @return string Wyczyszczony URL
     */
    public function sanitize_url(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        return esc_url_raw($url);
    }

    /**
     * Walidacja całego formularza
     *
     * @param array $input Dane z formularza
     * @return array<string,string> Tablica błędów
     */
    public function validate_form(array $input): array
    {
        $errors = [];

        if (empty($input['ppgm_email_contact'])) {
            $errors['ppgm_email_contact'] = __('Email kontaktowy jest wymagany', 'privacy-policy-gdpr-manager');
        } elseif (!$this->validate_email($input['ppgm_email_contact'])) {
            $errors['ppgm_email_contact'] = __('Nieprawidłowy format adresu email', 'privacy-policy-gdpr-manager');
        }

        if (!empty($input['ppgm_privacy_url']) && !$this->validate_url($input['ppgm_privacy_url'])) {
            $errors['ppgm_privacy_url'] = __('Nieprawidłowy format URL polityki prywatności', 'privacy-policy-gdpr-manager');
        }

        return $errors;
    }
}