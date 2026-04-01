# KW CMS Custom CSS — Moduł PrestaShop 9.0.3

## Informacje ogólne

| Parametr | Wartość |
|---|---|
| **Nazwa modułu** | `kw_cmscustomcss` |
| **Wersja** | 1.0.0 |
| **Autor** | KEDAR-WIHA.pl |
| **Platforma** | PrestaShop 9.0.3 |
| **Motyw** | Optima 3.3.0 (theme_optima) |
| **Framework** | Bootstrap 4.x |
| **Licencja** | AFL 3.0 |

## Co robi moduł?

Dodaje możliwość przypisania **indywidualnego CSS** do każdej strony CMS w PrestaShop.
CSS jest przechowywany w dedykowanej tabeli bazy danych i wstrzykiwany do sekcji `<head>`
**wyłącznie** na tej konkretnej stronie CMS. Puste pole = zero output.

## Architektura

### Tabela bazy danych

```sql
PREFIX_kw_cms_custom_css
├── id_kw_cms_custom_css  INT(11) UNSIGNED AUTO_INCREMENT  (PK)
├── id_cms                INT(11) UNSIGNED                 (UNIQUE INDEX)
├── custom_css            LONGTEXT
├── date_add              DATETIME
└── date_upd              DATETIME
```

Indeks `UNIQUE` na `id_cms` gwarantuje:
- Jeden rekord CSS na stronę CMS (brak duplikatów)
- Szybkie wyszukiwanie O(1) po ID strony

### Hooki

| Hook | Typ | Cel |
|---|---|---|
| `displayHeader` | Frontend | Wstrzyknięcie `<style>` do `<head>` — linia 86 w `head.tpl` Optima (`{$HOOK_HEADER nofilter}`) |
| `displayBackOfficeHeader` | Admin | Ładowanie `admin.css` i `admin.js` na stronie konfiguracji modułu |
| `actionObjectCmsUpdateAfter` | System | Invalidacja cache CSS po edycji strony CMS |
| `actionObjectCmsAddAfter` | System | Reset cache po dodaniu nowej strony CMS |
| `actionObjectCmsDeleteAfter` | System | Automatyczne czyszczenie CSS po usunięciu strony CMS |

### Jak działa wstrzyknięcie CSS (Frontend Flow)

```
1. Użytkownik otwiera stronę CMS (np. /content/5-o-firmie)
2. PrestaShop renderuje layout → head.tpl → {$HOOK_HEADER nofilter}
3. Hook `displayHeader` odpala się w module kw_cmscustomcss
4. Moduł sprawdza: czy to strona CMS? → getCurrentCmsId()
   - CmsController → $controller->cms->id
   - Fallback: page_name === 'cms' && Tools::getValue('id_cms')
5. Jeśli ID CMS > 0 → getCssFromDb(idCms)
   - Statyczny cache (array) → unikamy duplikatów query
6. Jeśli CSS nie pusty → output <style data-kw-cms-custom="5">...</style>
7. Guard: static $cssRendered = true → zapobiega podwójnemu renderowaniu
```

### Kolejność ładowania CSS (Optima head.tpl)

```
1. <meta charset>
2. SEO meta tags
3. Open Graph tags
4. {block name='stylesheets'} → theme.css, custom-style.css, itp.
5. {block name='javascript_head'}
6. {block name='hook_header'} → {$HOOK_HEADER nofilter}
   ↑ TUTAJ wstrzykiwany jest Custom CSS z modułu
7. {block name='hook_extra'}
```

Custom CSS ładuje się **po** wszystkich bazowych stylach motywu,
co oznacza naturalną wyższą specyficzność w kaskadzie CSS.
Nie wymaga nadmiarowego `!important`.

## Bezpieczeństwo

### Sanityzacja CSS

Moduł implementuje wielowarstwową walidację CSS:

1. **Null bytes** — usuwane (`\x00-\x08`, `\x0B`, `\x0C`, `\x0E-\x1F`, `\x7F`)
2. **Niebezpieczne wzorce** — blokowane regex:
   - `expression()` — IE CSS injection
   - `javascript:` / `vbscript:` — URI scheme injection
   - `data:text/html` / `data:application/javascript` — data URI injection
   - `-moz-binding:` — Firefox XBL binding
   - `behavior:` — IE HTC behavior
   - `@import` — zapobiega ładowaniu zewnętrznych stylesheets
   - `@charset` / `@namespace` — potencjalnie niebezpieczne
   - Unicode escape (`\\00`) — bypass protection
3. **Balans nawiasów** — sprawdzanie `{` vs `}` count
4. **At-rules whitelist** — dozwolone: `@media`, `@supports`, `@keyframes`, `@font-face`, `@layer`, `@container`
5. **HTML tags** — strip_tags()
6. **HTML komentarze** — `<!-- -->` usuwane (XSS vector w `<style>`)
7. **Limit długości** — 500,000 znaków max

### CSRF Protection

- Token AdminModules w formularzu
- Weryfikacja przy każdym submit/delete

### Parameterized Queries

- Wszystkie operacje DB używają `(int)` casting lub `DbQuery` builder
- `pSQL()` dla wartości tekstowych

## Instalacja

### Docker / Development

```bash
# Skopiuj katalog modułu do PrestaShop
cp -r kw_cmscustomcss/ /var/www/html/modules/

# Lub zamontuj jako volume w docker-compose.yml
volumes:
  - ./modules/kw_cmscustomcss:/var/www/html/modules/kw_cmscustomcss

# Zainstaluj z CLI
php bin/console prestashop:module install kw_cmscustomcss

# Lub z panelu admin:
# Moduły → Module Manager → Upload a module → kw_cmscustomcss.zip
```

### Produkcja

1. Przesłanie katalogu `kw_cmscustomcss/` do `/modules/` na serwerze
2. Panel admin → Moduły → Wyszukaj "KW CMS Custom CSS" → Zainstaluj
3. Kliknij "Konfiguruj" → Wybierz stronę CMS → Wklej CSS → Zapisz

## Konfiguracja

### Panel admin

Moduły → Module Manager → KW CMS Custom CSS → **Konfiguruj**

Interfejs zawiera:
1. **Panel informacyjny** — opis funkcji i ograniczeń
2. **Formularz edycji** — select strony CMS + textarea z CSS
3. **Lista zapisanych CSS** — tabela z edycją/usuwaniem
4. **Podręcznik zmiennych CSS** — szybka referencja `:root` zmiennych KEDAR-WIHA

### Zmienne CSS (KEDAR-WIHA Brand)

Rekomendowane zmienne do użycia w custom CSS:

```css
/* Kolory marki */
var(--color-theme-primary)    /* #8B0000 — KEDAR Crimson */
var(--color-theme-secondary)  /* #0056A3 — VDE Steel Blue */
var(--color-theme-three)      /* #ca0000 — Action Red */
var(--color-accent-yellow)    /* #FFD700 — WIHA Yellow */
var(--color-heading-primary)  /* #2D2D2D — Dark Charcoal */

/* Tła */
var(--background-theme-color) /* #f1f1f1 */
var(--color-bg-white)         /* #ffffff */
var(--color-bg-dark)          /* #2a2a2a */

/* Buttony */
var(--button-theme-color)       /* #8B0000 */
var(--button-theme-color-hover) /* #ca0000 */

/* Typografia */
var(--font-family-heading)  /* 'Montserrat', sans-serif */
var(--font-family-body)     /* 'Inter', sans-serif */
```

## Customization Points

### 1. Modyfikacja sanityzacji CSS

W `kw_cmscustomcss.php`:
- `ALLOWED_AT_RULES` — dodaj/usuń dozwolone @rules
- `DANGEROUS_PATTERNS` — dodaj/modyfikuj blokowane wzorce regex
- `MAX_CSS_LENGTH` — zmień limit długości CSS

### 2. Rozszerzenie na inne typy stron

Obecna wersja obsługuje wyłącznie strony CMS. Aby rozszerzyć na inne typy:
1. Dodaj nową kolumnę `page_type` do tabeli
2. Zmodyfikuj `getCurrentCmsId()` → `getCurrentPageId()` z obsługą różnych kontrolerów
3. Zarejestruj moduł na odpowiednie hooki akcji (np. `actionObjectCategoryUpdateAfter`)

### 3. Edytor kodu z syntax highlighting

Aby dodać CodeMirror lub Monaco Editor do textarea:
1. Dodaj bibliotekę do `views/js/`
2. Zainicjalizuj w `admin.js` po `DOMContentLoaded`
3. Zsynchronizuj wartość z hidden textarea przed submit

### 4. Wersjonowanie CSS

Aby dodać historię wersji CSS:
1. Utwórz tabelę `PREFIX_kw_cms_custom_css_history`
2. Dodaj hook `actionBeforeCmsCustomCssSave` → kopiuj aktualny rekord do historii
3. Dodaj interfejs "przywróć poprzednią wersję" w panelu admin

## Struktura plików

```
kw_cmscustomcss/
├── config.xml                          # Metadane modułu PrestaShop
├── index.php                           # Security: prevent directory listing
├── kw_cmscustomcss.php                 # Główna klasa modułu (hooki, logika, DB)
├── LICENSE.md                          # AFL 3.0
├── logo.png                            # Logo modułu (opcjonalne)
├── README.md                           # Ten plik
├── sql/
│   ├── index.php
│   ├── install.sql                     # CREATE TABLE
│   └── uninstall.sql                   # DROP TABLE
├── src/
│   ├── index.php
│   └── Repository/
│       └── index.php
├── views/
│   ├── index.php
│   ├── css/
│   │   ├── admin.css                   # Style panelu konfiguracji BO
│   │   └── index.php
│   ├── js/
│   │   ├── admin.js                    # JS: counter, tab support, validation
│   │   └── index.php
│   └── templates/
│       ├── index.php
│       ├── admin/
│       │   ├── configure.tpl           # Szablon Smarty panelu konfiguracji
│       │   └── index.php
│       └── hook/
│           └── index.php
```

## Kompatybilność

- **PrestaShop**: 1.7.8.0 — 9.x
- **PHP**: 8.1+
- **MySQL**: 5.7+ / MariaDB 10.3+
- **Motyw**: Optima 3.3.0 (testowane), inne motywy kompatybilne (hook `displayHeader` jest standardowy)

## Changelog

### 1.0.0 (2025)
- Pierwsza wersja produkcyjna
- Pełna sanityzacja CSS z whitelist at-rules
- Panel konfiguracji z listą stron CMS
- Automatyczne czyszczenie CSS przy usuwaniu stron CMS
- Statyczny cache zapytań DB
- Guard pattern zapobiegający duplikacji output
- CSRF protection
- Mobile-first responsive admin panel
