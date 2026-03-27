# Privacy Policy GDPR Manager

Kompleksowy plugin WordPress do zarządzania polityką prywatności i zgodnością z RODO/GDPR.

## Wymagania

- WordPress 6.0+
- PHP 8.1+
- MySQL 8.0+ lub MariaDB 10.4+

## Instalacja

### Manualna

1. Pobierz folder `privacy-policy-gdpr-manager`
2. Wgraj do `/wp-content/plugins/`
3. Aktywuj w panelu WordPress
4. Przejdź do **Ustawienia → Privacy Policy GDPR**
5. Skonfiguruj email kontaktowy i URL polityki prywatności

### Docker (Development)

```bash
# Klonuj repozytorium
git clone https://github.com/piotroq/privacy-policy-gdpr-manager.git

# Przejdź do katalogu
cd privacy-policy-gdpr-manager

# Uruchom środowisko
docker-compose up -d

# Dostęp do WordPress: http://localhost:8080
# Dostęp do phpMyAdmin: http://localhost:8081