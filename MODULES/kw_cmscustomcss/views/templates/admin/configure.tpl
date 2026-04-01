{**
 * KEDAR-WIHA.pl — kw_cmscustomcss
 * Szablon konfiguracji modułu w panelu admina.
 *
 * Bootstrap 4.x kompatybilny (PrestaShop BO).
 * Mobile-first responsive.
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2025 KEDAR-WIHA.pl
 * @version   1.0.0
 *}

{* ======================================================================
   PANEL INFORMACYJNY
   ====================================================================== *}
<div class="panel kw-panel-info" id="kw-css-info-panel">
    <div class="panel-heading">
        <i class="icon-info-circle"></i> {l s='KW CMS Custom CSS — Informacje' mod='kw_cmscustomcss'}
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <p>
                <strong>{l s='Moduł pozwala na dodanie niestandardowego CSS per strona CMS.' mod='kw_cmscustomcss'}</strong><br>
                {l s='CSS jest wstrzykiwany do sekcji &lt;head&gt; tylko na wybranej stronie CMS, po załadowaniu bazowych stylów motywu Optima.' mod='kw_cmscustomcss'}
            </p>
            <hr>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='CSS ładuje się PO stylach motywu — nadpisuje bazowe style Optima' mod='kw_cmscustomcss'}
            </p>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='Puste pole = zero output (brak pustych tagów &lt;style&gt;)' mod='kw_cmscustomcss'}
            </p>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='Automatyczne czyszczenie CSS przy usuwaniu strony CMS' mod='kw_cmscustomcss'}
            </p>
            <p class="mb-0">
                <i class="icon-check text-success"></i> {l s='Sanityzacja i walidacja CSS przy każdym zapisie' mod='kw_cmscustomcss'}
            </p>
        </div>

        <div class="alert alert-warning">
            <p class="mb-1">
                <strong><i class="icon-warning"></i> {l s='Wskazówki:' mod='kw_cmscustomcss'}</strong>
            </p>
            <ul class="mb-0">
                <li>{l s='Używaj zmiennych CSS z :root (np. var(--color-theme-primary)) dla spójności z brandem KEDAR-WIHA.' mod='kw_cmscustomcss'}</li>
                <li>{l s='Stosuj selektory .page-cms-{ID} dla unikalnej specyficzności per strona.' mod='kw_cmscustomcss'}</li>
                <li>{l s='Pisz CSS mobile-first — bazowe style na mobile, @media min-width dla desktop.' mod='kw_cmscustomcss'}</li>
                <li>{l s='Dozwolone @rules: @media, @supports, @keyframes, @font-face, @layer, @container.' mod='kw_cmscustomcss'}</li>
                <li>{l s='Zablokowane: @import, @charset, expression(), javascript:, data: URI.' mod='kw_cmscustomcss'}</li>
            </ul>
        </div>
    </div>
</div>

{* ======================================================================
   FORMULARZ EDYCJI CSS
   ====================================================================== *}
<div class="panel kw-panel-form" id="kw-css-form-panel">
    <div class="panel-heading">
        <i class="icon-code"></i> {l s='Edycja Custom CSS' mod='kw_cmscustomcss'}
    </div>

    <form id="kw_cms_custom_css_form"
          class="defaultForm form-horizontal"
          action="{$kw_form_action|escape:'htmlall':'UTF-8'}"
          method="post"
          enctype="multipart/form-data">

        {* Hidden CSRF token — przesyłany via POST, niezależny od URL GET params *}
        <input type="hidden" name="kw_admin_token" value="{$kw_admin_token|escape:'htmlall':'UTF-8'}" />

        <div class="form-wrapper">
            {* ----- Select: strona CMS ----- *}
            <div class="form-group">
                <label class="control-label col-lg-3 required" for="KW_CMS_PAGE_ID">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Wybierz stronę CMS, dla której chcesz dodać lub edytować custom CSS.' mod='kw_cmscustomcss'}">
                        {l s='Strona CMS' mod='kw_cmscustomcss'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <select name="KW_CMS_PAGE_ID"
                            id="KW_CMS_PAGE_ID"
                            class="form-control kw-cms-select"
                            required>
                        <option value="0">{l s='— Wybierz stronę CMS —' mod='kw_cmscustomcss'}</option>
                        {foreach from=$kw_cms_pages item=page}
                            <option value="{$page.id_cms|intval}"
                                    {if $kw_selected_cms_id == $page.id_cms}selected="selected"{/if}
                                    {if !$page.active}class="kw-cms-inactive"{/if}>
                                #{$page.id_cms|intval} — {$page.meta_title|escape:'htmlall':'UTF-8'}
                                {if !$page.active} [{l s='nieaktywna' mod='kw_cmscustomcss'}]{/if}
                            </option>
                        {/foreach}
                    </select>
                    <p class="help-block">
                        {l s='Po wybraniu strony, możesz załadować jej aktualny CSS klikając „Załaduj CSS".' mod='kw_cmscustomcss'}
                    </p>

                    {* Przycisk szybkiego ładowania CSS *}
                    <button type="button"
                            id="kw-load-css-btn"
                            class="btn btn-default btn-sm mt-2"
                            title="{l s='Załaduj zapisany CSS dla wybranej strony' mod='kw_cmscustomcss'}">
                        <i class="icon-download"></i> {l s='Załaduj CSS' mod='kw_cmscustomcss'}
                    </button>
                </div>
            </div>

            {* ----- Textarea: Custom CSS ----- *}
            <div class="form-group">
                <label class="control-label col-lg-3" for="KW_CMS_CUSTOM_CSS">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Wklej custom CSS, który zostanie wstrzyknięty do <head> wybranej strony CMS. Puste pole = brak dodatkowych stylów.' mod='kw_cmscustomcss'}">
                        {l s='Custom CSS' mod='kw_cmscustomcss'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="kw-css-editor-wrapper">
                        <div class="kw-css-editor-toolbar">
                            <span class="kw-css-label">
                                <i class="icon-css3"></i> CSS
                            </span>
                            <span class="kw-css-counter" id="kw-css-char-count">
                                0 / {$kw_max_css_length|intval} {l s='znaków' mod='kw_cmscustomcss'}
                            </span>
                        </div>
                        <textarea name="KW_CMS_CUSTOM_CSS"
                                  id="KW_CMS_CUSTOM_CSS"
                                  class="form-control kw-css-textarea"
                                  rows="20"
                                  spellcheck="false"
                                  autocomplete="off"
                                  placeholder="{l s='/* Wklej tutaj custom CSS dla wybranej strony CMS */

/* Przykład: nadpisanie tła sekcji */
.page-cms .page-content {
    background: var(--color-bg-white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
}

/* Mobile-first: bazowe style na mobile */
.kw-custom-section {
    padding: 1rem;
}

/* Desktop: nadpisanie dla większych ekranów */
@media (min-width: 768px) {
    .kw-custom-section {
        padding: 3rem;
    }
}' mod='kw_cmscustomcss'}">{$kw_current_css}</textarea>
                    </div>
                    <p class="help-block">
                        {l s='Max' mod='kw_cmscustomcss'} {$kw_max_css_length|intval} {l s='znaków. Dozwolone: selektory CSS, @media, @supports, @keyframes, @font-face, @layer, @container.' mod='kw_cmscustomcss'}
                    </p>
                </div>
            </div>
        </div>

        {* ----- Przyciski zapisu ----- *}
        <div class="panel-footer">
            <button type="submit"
                    name="submitKwCmsCustomCss"
                    class="btn btn-default pull-right"
                    id="kw-save-btn">
                <i class="process-icon-save"></i> {l s='Zapisz CSS' mod='kw_cmscustomcss'}
            </button>
        </div>
    </form>
</div>

{* ======================================================================
   LISTA ZAPISANYCH CSS
   ====================================================================== *}
<div class="panel kw-panel-list" id="kw-css-list-panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {l s='Zapisane style CSS' mod='kw_cmscustomcss'}
        <span class="badge badge-info">{$kw_saved_entries|count}</span>
    </div>

    {if $kw_saved_entries|count > 0}
        <div class="table-responsive">
            <table class="table table-striped table-hover kw-css-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">{l s='ID CMS' mod='kw_cmscustomcss'}</th>
                        <th>{l s='Tytuł strony' mod='kw_cmscustomcss'}</th>
                        <th class="text-center" style="width: 120px;">{l s='Rozmiar CSS' mod='kw_cmscustomcss'}</th>
                        <th class="text-center" style="width: 170px;">{l s='Ostatnia edycja' mod='kw_cmscustomcss'}</th>
                        <th class="text-center" style="width: 160px;">{l s='Akcje' mod='kw_cmscustomcss'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$kw_saved_entries item=entry}
                        <tr>
                            <td class="text-center">
                                <span class="badge">{$entry.id_cms|intval}</span>
                            </td>
                            <td>
                                {if $entry.cms_title}
                                    {$entry.cms_title|escape:'htmlall':'UTF-8'}
                                {else}
                                    <em class="text-muted">{l s='(brak tytułu)' mod='kw_cmscustomcss'}</em>
                                {/if}
                            </td>
                            <td class="text-center">
                                <span class="kw-css-size {if $entry.css_length > 10000}text-warning{else}text-muted{/if}">
                                    {($entry.css_length / 1024)|number_format:1} KB
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{$entry.date_upd|escape:'htmlall':'UTF-8'}</span>
                            </td>
                            <td class="text-center">
                                {* Przycisk Edytuj *}
                                <a href="{$kw_form_action|escape:'htmlall':'UTF-8'}&editKwCmsCustomCss=1&id_cms_edit={$entry.id_cms|intval}"
                                   class="btn btn-default btn-sm"
                                   title="{l s='Edytuj CSS' mod='kw_cmscustomcss'}">
                                    <i class="icon-pencil"></i> {l s='Edytuj' mod='kw_cmscustomcss'}
                                </a>

                                {* Przycisk Usuń *}
                                <a href="{$kw_form_action|escape:'htmlall':'UTF-8'}&deleteKwCmsCustomCss=1&id_cms_delete={$entry.id_cms|intval}"
                                   class="btn btn-default btn-sm kw-delete-btn"
                                   title="{l s='Usuń CSS' mod='kw_cmscustomcss'}"
                                   onclick="return confirm('{l s='Czy na pewno chcesz usunąć custom CSS dla tej strony?' mod='kw_cmscustomcss' js=1}');">
                                    <i class="icon-trash"></i>
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="panel-body">
            <div class="alert alert-info">
                <i class="icon-info-circle"></i>
                {l s='Brak zapisanych stylów CSS. Wybierz stronę CMS i dodaj swój pierwszy custom CSS.' mod='kw_cmscustomcss'}
            </div>
        </div>
    {/if}
</div>

{* ======================================================================
   QUICK REFERENCE — CSS Variables
   ====================================================================== *}
<div class="panel kw-panel-reference" id="kw-css-reference-panel">
    <div class="panel-heading kw-collapsible" data-toggle="collapse" data-target="#kw-reference-body" aria-expanded="false">
        <i class="icon-book"></i> {l s='Podręcznik — Zmienne CSS :root (KEDAR-WIHA Brand)' mod='kw_cmscustomcss'}
        <span class="pull-right"><i class="icon-chevron-down"></i></span>
    </div>
    <div class="panel-body collapse" id="kw-reference-body">
        <div class="row">
            <div class="col-md-4">
                <h4>{l s='Kolory marki' mod='kw_cmscustomcss'}</h4>
                <pre class="kw-code-block">--color-theme-primary: #8B0000;
--color-theme-secondary: #0056A3;
--color-theme-three: #ca0000;
--color-accent-yellow: #FFD700;
--color-heading-primary: #2D2D2D;</pre>
            </div>
            <div class="col-md-4">
                <h4>{l s='Tła i bordery' mod='kw_cmscustomcss'}</h4>
                <pre class="kw-code-block">--background-theme-color: #f1f1f1;
--color-bg-white: #ffffff;
--color-bg-light: #f6f6f6;
--color-bg-dark: #2a2a2a;
--border-color-light: #e8e8e8;
--border-color-focus: #8B0000;</pre>
            </div>
            <div class="col-md-4">
                <h4>{l s='Buttony i typografia' mod='kw_cmscustomcss'}</h4>
                <pre class="kw-code-block">--button-theme-color: #8B0000;
--button-theme-color-hover: #ca0000;
--font-family-heading: 'Montserrat';
--font-family-body: 'Inter';
--font-size-base: 0.9375rem;</pre>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h4>{l s='Wzorzec selektora per strona CMS' mod='kw_cmscustomcss'}</h4>
                <pre class="kw-code-block">/* CMS strona o ID 5 */
.page-cms.page-cms-5 .page-content {
    /* Twoje style tutaj */
}

/* Dla wyższej specyficzności */
body.cms-id-5 .page-content.page-cms {
    /* Override style */
}</pre>
            </div>
            <div class="col-md-6">
                <h4>{l s='Mobile-first pattern' mod='kw_cmscustomcss'}</h4>
                <pre class="kw-code-block">/* Bazowe style = mobile */
.kw-section { padding: 1rem; }

/* Tablet */
@media (min-width: 768px) {
    .kw-section { padding: 2rem; }
}

/* Desktop */
@media (min-width: 1024px) {
    .kw-section { padding: 3rem; }
}</pre>
            </div>
        </div>
    </div>
</div>

{* ======================================================================
   STOPKA Z WERSJĄ
   ====================================================================== *}
<div class="panel-footer text-center text-muted kw-module-footer">
    <small>
        <strong>KW CMS Custom CSS</strong> v{$kw_module_version|escape:'htmlall':'UTF-8'}
        &middot; KEDAR-WIHA.pl
        &middot; PrestaShop 9.0.3 + Optima 3.3.0
    </small>
</div>
