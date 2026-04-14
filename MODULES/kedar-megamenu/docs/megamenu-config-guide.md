# KEDAR-WIHA.pl — Kompletny Przewodnik Konfiguracji Megamenu
## PrestaShop 9.0.3 | Motyw Optima 3.3.0 | Moduły: pos_megamenu + pos_vertical_megamenu

---

## SPIS TREŚCI
1. [Wymagania i przygotowanie](#1-wymagania-i-przygotowanie)
2. [Wgranie plików CSS i JS](#2-wgranie-plikow-css-i-js)
3. [Konfiguracja Horizontal Megamenu (CMS)](#3-horizontal-megamenu-konfiguracja)
4. [Konfiguracja Vertical Megamenu (Sklep)](#4-vertical-megamenu-konfiguracja)
5. [Mapowanie kategorii sklepu](#5-mapowanie-kategorii)
6. [Testowanie i weryfikacja](#6-testowanie-i-weryfikacja)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. WYMAGANIA I PRZYGOTOWANIE

### Pliki do wgrania na serwer:
```
/themes/kedar_wiha/assets/custom/css/megamenu-kedar.css   ← nowy plik
/themes/kedar_wiha/assets/custom/js/megamenu-init.js       ← nowy plik
```

### Struktura pliku custom-style.css (istniejący):
Plik `megamenu-kedar.css` jest **dołączany przez motyw** — dodaj import w custom-style.css:
```css
/* Na końcu custom-style.css — po wszystkich sekcjach */
/* @import "megamenu-kedar.css"; */
/* LUB wgraj przez moduł insertcodeheadfooter jako osobny <link> */
```

### Opcja A — przez insertcodeheadfooter (zalecana):
Wklej w polu **HEAD Code**:
```html
<link rel="stylesheet" href="{$urls.theme_assets}custom/css/megamenu-kedar.css">
```

Wklej w polu **FOOTER Code** (przed `</body>`):
```html
<script src="{$urls.theme_assets}custom/js/megamenu-init.js" defer></script>
```

### Opcja B — bezpośrednio w child theme:
Edytuj plik `/themes/kedar_wiha/templates/_partials/head.tpl`:
```smarty
{* Megamenu KEDAR-WIHA — dodaj przed </head> *}
<link rel="stylesheet" href="{$urls.theme_assets}custom/css/megamenu-kedar.css">
```

Edytuj `/themes/kedar_wiha/templates/layouts/layout-both-columns.tpl` (linia ~161):
```smarty
{* Megamenu JS — po jQuery, przed </body> *}
<script src="{$urls.theme_assets}custom/js/megamenu-init.js"></script>
```

---

## 2. WGRANIE PLIKÓW CSS I JS

### Przez FTP / File Manager:
1. Przejdź do `/themes/kedar_wiha/assets/custom/`
2. W katalogu `css/` — wgraj `megamenu-kedar.css`
3. W katalogu `js/` (utwórz jeśli nie istnieje) — wgraj `megamenu-init.js`
4. Uprawnienia plików: `644`

### Weryfikacja wgrania:
Otwórz w przeglądarce:
```
https://kedarwiha.smarthost.pl/kedar-wiha.pl/themes/kedar_wiha/assets/custom/css/megamenu-kedar.css
https://kedarwiha.smarthost.pl/kedar-wiha.pl/themes/kedar_wiha/assets/custom/js/megamenu-init.js
```
Oba powinny zwracać kod (200 OK).

---

## 3. HORIZONTAL MEGAMENU — KONFIGURACJA KROK PO KROKU

### 3.1 Dostęp do modułu
```
Panel Admin → POSTHEMES → Modules → Pos Megamenu
LUB
Panel Admin → Moduły → Zainstalowane → szukaj "megamenu"
```

### 3.2 Ustawienia ogólne modułu pos_megamenu

| Parametr | Wartość |
|----------|---------|
| Enable Megamenu | ✅ Włączony |
| Menu Type | Horizontal |
| Effect | Slide / Fade |
| Hover delay (ms) | 100 |
| Background color | Dziedziczone z CSS |
| Font size | Dziedziczone z CSS |

### 3.3 Struktura pozycji menu — KEDAR-WIHA

Poniżej lista wszystkich pozycji głównych do skonfigurowania:

---

#### POZYCJA 1: "Strona Główna"
```
Link:     /pl/ (lub {baseurl}pl/)
Label:    Strona Główna
Icon:     fas fa-home (jeśli obsługuje)
Type:     Simple link (bez megamenu)
Badge:    brak
```

---

#### POZYCJA 2: "Wkrętaki VDE"
```
Link:     /pl/45-wkretaki-vde
Label:    Wkrętaki VDE
Badge:    HOT (kolor czerwony)
Type:     Megamenu columns
Columns:  3
```

**Konfiguracja kolumn:**

**Kolumna 1 — "Rodzaj zakończenia":**
```
Title:  Rodzaj zakończenia (HTML: <span class="mm-col-title"><i class="fas fa-screwdriver"></i> Rodzaj zakończenia</span>)
Links:
  - Wkrętaki płaskie VDE → /pl/46-wkretaki-plaskie-vde
  - Wkrętaki krzyżakowe PH → /pl/47-wkretaki-ph-vde
  - Wkrętaki krzyżakowe PZ → /pl/48-wkretaki-pz-vde
  - Wkrętaki Torx VDE → /pl/49-wkretaki-torx-vde
  - Wkrętaki Hex VDE → /pl/50-wkretaki-hex-vde
```

**Kolumna 2 — "Linie produktowe":**
```
Title:  Linie produktowe
Links:
  - slimTechnology [NEW] → /pl/52-slim-technology
  - speedE® → /pl/53-speed-e
  - SLIMBit VDE → /pl/54-slimbit-vde
  
Podsekcja:
  - Zestawy VDE → /pl/55-zestawy-wkretakow-vde
  - Zestawy instalatorskie → /pl/56-zestawy-instalatorskie
```

**Kolumna 3 — HTML Content (panel promo):**
```html
<!-- WKLEJ HTML z pliku horizontal-megamenu-cms.html → sekcja FLYOUT 1 promo panel -->
<div class="vmm-flyout-promo">
  <div class="vmm-flyout-promo-content">
    <span class="badge">Bestseller</span>
    <h4>WIHA slimLine VDE<br>Kompletny zestaw</h4>
    <p>7-częściowy zestaw wkrętaków VDE izolowanych do 1 000 V.</p>
    <a href="/pl/55-zestawy-wkretakow-vde" class="vmm-promo-link">
      <i class="fas fa-shopping-cart"></i> Zobacz zestawy
    </a>
  </div>
</div>
```

---

#### POZYCJA 3: "Narzędzia i klucze"
```
Link:     /pl/60-klucze-dynamometryczne
Label:    Narzędzia i klucze
Badge:    brak
Type:     Megamenu columns
Columns:  3
```
*[Struktura analogiczna do Flyout 2 w vertical-megamenu-shop.html]*

---

#### POZYCJA 4: "Zestawy narzędzi"
```
Link:     /pl/80-zestawy-narzedzi
Label:    Zestawy narzędzi
Badge:    NEW (kolor zielony)
Type:     Megamenu columns
Columns:  2
```

---

#### POZYCJA 5: "O firmie" ← GŁÓWNY BLOK CMS
```
Link:     javascript:void(0) (lub /pl/content/o-firmie)
Label:    O firmie
Type:     HTML Content FULL
```

**HTML Content dla "O firmie":**
➡ Wklej **cały BLOK 1** z pliku `horizontal-megamenu-cms.html` (sekcja BLOK 1).

---

#### POZYCJA 6: "Certyfikaty i Normy"
```
Link:     /pl/content/deklaracja-zgodnosci
Label:    Certyfikaty & Normy
Type:     HTML Content
```

**HTML Content:**
➡ Wklej **BLOK 2** z pliku `horizontal-megamenu-cms.html`.

---

#### POZYCJA 7: "Informacje"
```
Link:     /pl/content/informacje-dla-klienta
Label:    Informacje
Type:     HTML Content
```

**HTML Content:**
➡ Wklej **BLOK 4** z pliku `horizontal-megamenu-cms.html`.

---

#### POZYCJA 8: "Kontakt"
```
Link:     /pl/kontakt
Label:    Kontakt
Type:     HTML Content
```

**HTML Content:**
➡ Wklej **BLOK 3** z pliku `horizontal-megamenu-cms.html`.

---

### 3.4 Jak wkleić HTML Content w pos_megamenu:

1. Przejdź do **Pozycja menu → Edytuj**
2. Zaznacz opcję **"Use HTML Content"** lub **"Enable Rich Content"** (zależnie od wersji)
3. Kliknij zakładkę **"HTML Content"**
4. W edytorze — przełącz na **"Source Code"** (ikona `<>`)
5. Wklej odpowiedni blok HTML
6. **WAŻNE**: Upewnij się że edytor NIE stripuje klas CSS!
7. Kliknij **Save / Zapisz**

---

## 4. VERTICAL MEGAMENU — KONFIGURACJA KROK PO KROKU

### 4.1 Dostęp do modułu
```
Panel Admin → POSTHEMES → Modules → Pos Vertical Megamenu
LUB
Panel Admin → Moduły → szukaj "vertical megamenu"
```

### 4.2 Ustawienia ogólne

| Parametr | Wartość |
|----------|---------|
| Enable | ✅ Włączony |
| Title | Wszystkie kategorie |
| Show on homepage | ✅ Tak |
| Show on all pages | Opcjonalnie |
| Hover effect | Flyout Right |
| Animation | Slide |
| Width | 260px |

### 4.3 Header HTML (tytuł menu)
W polu **"Header HTML Content"** wklej:
```html
<div class="vmm-header">
  <i class="fas fa-th-large"></i>
  <span class="vmm-header-title">Wszystkie kategorie</span>
  <span class="vmm-header-subtitle">WIHA</span>
</div>
```

### 4.4 Struktura kategorii i flyoutów

Poniżej każda kategoria pierwszego poziomu z przypisanym flyoutem:

---

**KATEGORIA 1: Wkrętaki VDE Izolowane**
```
Link:      /pl/45-wkretaki-vde
Label:     Wkrętaki VDE Izolowane
Icon:      fas fa-bolt
Badge:     HOT
Flyout:    ✅ Włączony
```
**HTML Flyout Content:**
➡ Wklej **FLYOUT 1** z pliku `vertical-megamenu-shop.html`.

---

**KATEGORIA 2: Klucze i narzędzia ręczne**
```
Link:      /pl/60-klucze-dynamometryczne
Label:     Klucze i narzędzia
Icon:      fas fa-wrench
Badge:     brak
Flyout:    ✅ Włączony
```
**HTML Flyout Content:**
➡ Wklej **FLYOUT 2** z pliku `vertical-megamenu-shop.html`.

---

**KATEGORIA 3: Zestawy narzędzi**
```
Link:      /pl/80-zestawy-narzedzi
Label:     Zestawy narzędzi
Icon:      fas fa-toolbox
Badge:     NEW
Flyout:    ✅ Włączony
```
**HTML Flyout Content:**
➡ Wklej **FLYOUT 3** z pliku `vertical-megamenu-shop.html`.

---

**KATEGORIA 4: Akcesoria i końcówki**
```
Link:      /pl/100-akcesoria
Label:     Akcesoria i końcówki
Icon:      fas fa-puzzle-piece
Badge:     brak
Flyout:    ✅ Włączony
```
**HTML Flyout Content:**
➡ Wklej **FLYOUT 4** z pliku `vertical-megamenu-shop.html`.

---

**KATEGORIA 5: Narzędzia do izolacji kabli** *(prosty link bez flyout)*
```
Link:      /pl/110-narzedzia-izolacja
Label:     Narzędzia do izolacji
Icon:      fas fa-scissors
Flyout:    ❌ Nie
```

---

**KATEGORIA 6: Zestawy PV i EV** *(prosty link)*
```
Link:      /pl/115-zestawy-pv-ev
Label:     Zestawy PV / EV
Icon:      fas fa-solar-panel
Badge:     NEW
Flyout:    ❌ Nie
```

---

## 5. MAPOWANIE KATEGORII

### Aktualne ID kategorii PrestaShop → zaktualizuj URL-e!

**WAŻNE**: URL-e w HTML-u są przykładowe. Zastąp je rzeczywistymi ID/slugami z Twojego PS9:

```
Panel Admin → Katalog → Kategorie → kliknij kategorię → skopiuj "Friendly URL"
```

Przykładowa tabela do uzupełnienia:

| Kategoria | URL (przykład) | ID PS | Rzeczywisty URL |
|-----------|---------------|-------|-----------------|
| Wkrętaki VDE | /pl/45-wkretaki-vde | ? | UZUPEŁNIJ |
| Klucze dynamometryczne | /pl/60-klucze | ? | UZUPEŁNIJ |
| Zestawy | /pl/80-zestawy | ? | UZUPEŁNIJ |
| Akcesoria | /pl/100-akcesoria | ? | UZUPEŁNIJ |

---

## 6. TESTOWANIE I WERYFIKACJA

### Checklist przed wdrożeniem:

#### Desktop (≥1024px):
- [ ] Horizontal megamenu — hover pokazuje dropdown
- [ ] Dropdown pojawia się z animacją (fade + slide)
- [ ] Podkreślenie na aktywnym elemencie
- [ ] Panel promo renderuje się poprawnie
- [ ] USP bar na dole panelu widoczny
- [ ] Vertical megamenu — hover na kategorii otwiera flyout po prawej
- [ ] Flyout zawiera siatka podkategorii
- [ ] Badge HOT pulsuje
- [ ] Ikony Font Awesome ładują się
- [ ] Kolory zgodne z brandbookiem (#8B0000, #0056A3)

#### Mobile (<1024px):
- [ ] Horizontal megamenu — accordion (klik otwiera)
- [ ] Brak widoczności flyoutów na hover
- [ ] Vertical megamenu — pełna szerokość
- [ ] Panele promo są ukryte (jak w CSS)
- [ ] Touch targets ≥48px
- [ ] Przewijanie bez problemów

#### Dostępność (WCAG 2.1 AA):
- [ ] Tab navigation przez menu działa
- [ ] Enter/Space otwiera dropdown
- [ ] Escape zamyka dropdown
- [ ] Focus visible na wszystkich elementach
- [ ] ARIA attributes poprawnie ustawione
- [ ] Skip link widoczny przy focus

#### Wydajność:
```
DevTools → Network → przeładuj stronę
CSS: megamenu-kedar.css ładuje się (200 OK)
JS: megamenu-init.js ładuje się (200 OK)
Brak błędów JS w Console
```

---

## 7. TROUBLESHOOTING

### Problem: Dropdown nie pojawia się na hover
**Przyczyna**: Konflikt z CSS Optimy (z-index lub overflow:hidden)
**Rozwiązanie**:
```css
/* Dodaj do megamenu-kedar.css */
#header .menu-content {
  overflow: visible !important;
}
.header-nav {
  overflow: visible !important;
}
```

### Problem: HTML content jest stripowany (tagi usunięte)
**Przyczyna**: Edytor TinyMCE stripuje nieznane atrybuty
**Rozwiązanie**: W panelu Optima → Settings → Editor → wyłącz "Clean HTML" lub użyj Source Code i zapisz

### Problem: Ikony Font Awesome nie renderują
**Przyczyna**: FA5 nie jest załadowane lub błędna klasa
**Rozwiązanie**: Sprawdź w DevTools:
```javascript
// Wklej w Console
document.querySelector('.fas') !== null // powinno zwrócić true
```
Jeśli false — dodaj ręcznie w head:
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
```

### Problem: Vertical flyout wychodzi poza ekran
**Przyczyna**: Brak miejsca po prawej stronie
**Rozwiązanie**: JS automatycznie wykrywa i przestawia flyout w lewo. Sprawdź czy `megamenu-init.js` jest załadowany.

### Problem: CSS zmienne :root nie działają w dropdown
**Przyczyna**: Znany bug — PrestaShop CMS editor nie resolwuje :root
**Rozwiązanie**: Już obsłużone — wszystkie style megamenu używają lokalnych var() z fallback values.

### Problem: Menu aktywne (podkreślenie) nie działa
**Przyczyna**: JS sprawdza pathname, ale URL może być inny
**Rozwiązanie**: Optima zazwyczaj dodaje klasę `.current` lub `.active` — zmień selektor w megamenu-init.js:
```javascript
markActivePage: function () {
  // Dodaj: sprawdź klasy Optimy
  $(KW_MM_CONFIG.selectors.megamenuItem).filter('.current, .active')
    .addClass('active');
}
```

### Problem: Megamenu działa ale brak animacji
**Przyczyna**: `prefers-reduced-motion: reduce` w systemie użytkownika (poprawne zachowanie!) LUB brak klasy `open` / CSS transition
**Rozwiązanie**: To zachowanie zgodne z WCAG — nie zmieniaj dla użytkowników z ograniczoną ruchomością.

---

## LINKI DO DOKUMENTACJI

- Optima Megamenu: https://ecolife.posthemes.com/doc/#module_config
- PrestaShop hooks: https://devdocs.prestashop-project.org/9/modules/concepts/hooks/
- Bootstrap 4 docs: https://getbootstrap.com/docs/4.6/
- Font Awesome 5: https://fontawesome.com/v5/search
- WCAG 2.1 guidelines: https://www.w3.org/WAI/WCAG21/quickref/

---

*Dokumentacja KEDAR-WIHA.pl Megamenu | Wersja 1.0.0 | 2025*
