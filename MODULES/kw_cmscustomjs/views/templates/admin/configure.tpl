{**
 * KEDAR-WIHA.pl — kw_cmscustomjs
 * Szablon konfiguracji modułu w panelu admina.
 * Bootstrap 4.x kompatybilny (PrestaShop BO). Mobile-first responsive.
 *
 * @author    KEDAR-WIHA.pl
 * @version   1.0.0
 *}

{* ======================================================================
   PANEL INFORMACYJNY
   ====================================================================== *}
<div class="panel kwjs-panel-info">
    <div class="panel-heading">
        <i class="icon-info-circle"></i> {l s='KW CMS Custom JS — Informacje' mod='kw_cmscustomjs'}
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <p>
                <strong>{l s='Moduł pozwala na dodanie niestandardowego JavaScript per strona CMS.' mod='kw_cmscustomjs'}</strong><br>
                {l s='JS jest wstrzykiwany przed &lt;/body&gt; tylko na wybranej stronie CMS, po załadowaniu jQuery, Bootstrap i skryptów motywu Optima.' mod='kw_cmscustomjs'}
            </p>
            <hr>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='JS ładuje się PO jQuery, Bootstrap i optima.js — pełny dostęp do $(...)' mod='kw_cmscustomjs'}
            </p>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='Automatyczne opakowanie w IIFE + try/catch (bezpieczna izolacja scope)' mod='kw_cmscustomjs'}
            </p>
            <p class="mb-1">
                <i class="icon-check text-success"></i> {l s='Puste pole = zero output (brak pustych tagów &lt;script&gt;)' mod='kw_cmscustomjs'}
            </p>
            <p class="mb-0">
                <i class="icon-check text-success"></i> {l s='Automatyczne czyszczenie JS przy usuwaniu strony CMS' mod='kw_cmscustomjs'}
            </p>
        </div>

        <div class="alert alert-warning">
            <p class="mb-1">
                <strong><i class="icon-warning"></i> {l s='Wskazówki:' mod='kw_cmscustomjs'}</strong>
            </p>
            <ul class="mb-0">
                <li>{l s='NIE umieszczaj tagów &lt;script&gt;&lt;/script&gt; — moduł dodaje je automatycznie.' mod='kw_cmscustomjs'}</li>
                <li>{l s='jQuery jest dostępne jako $ i jQuery — możesz używać $(".selector").' mod='kw_cmscustomjs'}</li>
                <li>{l s='Kod jest opakowany w IIFE z "use strict" — zmienne lokalne nie wyciekają do global scope.' mod='kw_cmscustomjs'}</li>
                <li>{l s='Błędy runtime są łapane przez try/catch i logowane w console.error — nie crashują strony.' mod='kw_cmscustomjs'}</li>
                <li>{l s='Zablokowane: eval(), document.cookie, document.write(), zagnieżdżone &lt;script&gt;, obfuskacja.' mod='kw_cmscustomjs'}</li>
            </ul>
        </div>
    </div>
</div>

{* ======================================================================
   FORMULARZ EDYCJI JS
   ====================================================================== *}
<div class="panel kwjs-panel-form" id="kwjs-form-panel">
    <div class="panel-heading">
        <i class="icon-code"></i> {l s='Edycja Custom JS' mod='kw_cmscustomjs'}
    </div>

    <form id="kw_cms_custom_js_form"
          class="defaultForm form-horizontal"
          action="{$kw_form_action|escape:'htmlall':'UTF-8'}"
          method="post"
          enctype="multipart/form-data">

        {* Hidden CSRF token — POST, niezależny od URL *}
        <input type="hidden" name="kw_admin_token" value="{$kw_admin_token|escape:'htmlall':'UTF-8'}" />

        <div class="form-wrapper">
            {* ----- Select: strona CMS ----- *}
            <div class="form-group">
                <label class="control-label col-lg-3 required" for="KW_CMS_PAGE_ID">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Wybierz stronę CMS, dla której chcesz dodać lub edytować custom JS.' mod='kw_cmscustomjs'}">
                        {l s='Strona CMS' mod='kw_cmscustomjs'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <select name="KW_CMS_PAGE_ID"
                            id="KW_CMS_PAGE_ID"
                            class="form-control kwjs-cms-select"
                            required>
                        <option value="0">{l s='— Wybierz stronę CMS —' mod='kw_cmscustomjs'}</option>
                        {foreach from=$kw_cms_pages item=page}
                            <option value="{$page.id_cms|intval}"
                                    {if $kw_selected_cms_id == $page.id_cms}selected="selected"{/if}
                                    {if !$page.active}class="kwjs-cms-inactive"{/if}>
                                #{$page.id_cms|intval} — {$page.meta_title|escape:'htmlall':'UTF-8'}
                                {if !$page.active} [{l s='nieaktywna' mod='kw_cmscustomjs'}]{/if}
                            </option>
                        {/foreach}
                    </select>
                    <p class="help-block">
                        {l s='Po wybraniu strony, możesz załadować jej aktualny JS klikając „Załaduj JS".' mod='kw_cmscustomjs'}
                    </p>

                    <button type="button"
                            id="kwjs-load-btn"
                            class="btn btn-default btn-sm mt-2"
                            title="{l s='Załaduj zapisany JS dla wybranej strony' mod='kw_cmscustomjs'}">
                        <i class="icon-download"></i> {l s='Załaduj JS' mod='kw_cmscustomjs'}
                    </button>
                </div>
            </div>

            {* ----- Textarea: Custom JS ----- *}
            <div class="form-group">
                <label class="control-label col-lg-3" for="KW_CMS_CUSTOM_JS">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Wklej custom JavaScript. NIE dodawaj tagów <script>. Kod będzie automatycznie opakowany w IIFE + try/catch.' mod='kw_cmscustomjs'}">
                        {l s='Custom JavaScript' mod='kw_cmscustomjs'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="kwjs-editor-wrapper">
                        <div class="kwjs-editor-toolbar">
                            <span class="kwjs-label">
                                <i class="icon-terminal"></i> JavaScript
                            </span>
                            <span class="kwjs-counter" id="kwjs-char-count">
                                0 / {$kw_max_js_length|intval} {l s='znaków' mod='kw_cmscustomjs'}
                            </span>
                        </div>
                        <textarea name="KW_CMS_CUSTOM_JS"
                                  id="KW_CMS_CUSTOM_JS"
                                  class="form-control kwjs-textarea"
                                  rows="20"
                                  spellcheck="false"
                                  autocomplete="off"
                                  placeholder="{l s='// Wklej tutaj custom JavaScript dla wybranej strony CMS
// NIE dodawaj tagów <script> — moduł robi to automatycznie
// jQuery jest dostępne jako $ i jQuery

// Przykład: animacja sekcji przy scroll
document.addEventListener(\"DOMContentLoaded\", function() {
    console.log(\"Custom JS loaded for this CMS page\");

    // jQuery example
    $(\".page-cms .page-content\").addClass(\"kw-animated\");
});' mod='kw_cmscustomjs'}">{$kw_current_js}</textarea>
                    </div>
                    <p class="help-block">
                        {l s='Max' mod='kw_cmscustomjs'} {$kw_max_js_length|intval} {l s='znaków. Nie wklejaj tagów &lt;script&gt;. jQuery ($) dostępne.' mod='kw_cmscustomjs'}
                    </p>
                </div>
            </div>
        </div>

        {* ----- Przyciski zapisu ----- *}
        <div class="panel-footer">
            <button type="submit"
                    name="submitKwCmsCustomJs"
                    class="btn btn-default pull-right"
                    id="kwjs-save-btn">
                <i class="process-icon-save"></i> {l s='Zapisz JS' mod='kw_cmscustomjs'}
            </button>
        </div>
    </form>
</div>

{* ======================================================================
   LISTA ZAPISANYCH JS
   ====================================================================== *}
<div class="panel kwjs-panel-list" id="kwjs-list-panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {l s='Zapisane skrypty JS' mod='kw_cmscustomjs'}
        <span class="badge badge-info">{$kw_saved_entries|count}</span>
    </div>

    {if $kw_saved_entries|count > 0}
        <div class="table-responsive">
            <table class="table table-striped table-hover kwjs-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">{l s='ID CMS' mod='kw_cmscustomjs'}</th>
                        <th>{l s='Tytuł strony' mod='kw_cmscustomjs'}</th>
                        <th class="text-center" style="width: 120px;">{l s='Rozmiar JS' mod='kw_cmscustomjs'}</th>
                        <th class="text-center" style="width: 170px;">{l s='Ostatnia edycja' mod='kw_cmscustomjs'}</th>
                        <th class="text-center" style="width: 160px;">{l s='Akcje' mod='kw_cmscustomjs'}</th>
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
                                    <em class="text-muted">{l s='(brak tytułu)' mod='kw_cmscustomjs'}</em>
                                {/if}
                            </td>
                            <td class="text-center">
                                <span class="kwjs-size {if $entry.js_length > 10000}text-warning{else}text-muted{/if}">
                                    {($entry.js_length / 1024)|number_format:1} KB
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{$entry.date_upd|escape:'htmlall':'UTF-8'}</span>
                            </td>
                            <td class="text-center">
                                <a href="{$kw_form_action|escape:'htmlall':'UTF-8'}&editKwCmsCustomJs=1&id_cms_edit={$entry.id_cms|intval}"
                                   class="btn btn-default btn-sm"
                                   title="{l s='Edytuj JS' mod='kw_cmscustomjs'}">
                                    <i class="icon-pencil"></i> {l s='Edytuj' mod='kw_cmscustomjs'}
                                </a>

                                <a href="{$kw_form_action|escape:'htmlall':'UTF-8'}&deleteKwCmsCustomJs=1&id_cms_delete={$entry.id_cms|intval}"
                                   class="btn btn-default btn-sm kwjs-delete-btn"
                                   title="{l s='Usuń JS' mod='kw_cmscustomjs'}"
                                   onclick="return confirm('{l s='Czy na pewno chcesz usunąć custom JS dla tej strony?' mod='kw_cmscustomjs' js=1}');">
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
                {l s='Brak zapisanych skryptów JS. Wybierz stronę CMS i dodaj swój pierwszy custom JavaScript.' mod='kw_cmscustomjs'}
            </div>
        </div>
    {/if}
</div>

{* ======================================================================
   QUICK REFERENCE — JS Patterns
   ====================================================================== *}
<div class="panel kwjs-panel-reference">
    <div class="panel-heading kwjs-collapsible" data-toggle="collapse" data-target="#kwjs-reference-body" aria-expanded="false">
        <i class="icon-book"></i> {l s='Podręcznik — Wzorce JavaScript dla stron CMS' mod='kw_cmscustomjs'}
        <span class="pull-right"><i class="icon-chevron-down"></i></span>
    </div>
    <div class="panel-body collapse" id="kwjs-reference-body">
        <div class="row">
            <div class="col-md-4">
                <h4>{l s='jQuery Ready' mod='kw_cmscustomjs'}</h4>
                <pre class="kwjs-code-block">// jQuery jest od razu dostępne
$(".page-cms .page-content")
  .css("opacity", 0)
  .animate({ opacity: 1 }, 600);

// Lub z document ready
$(document).ready(function() {
  console.log("CMS page ready");
});</pre>
            </div>
            <div class="col-md-4">
                <h4>{l s='Scroll Animation' mod='kw_cmscustomjs'}</h4>
                <pre class="kwjs-code-block">// Intersection Observer
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList
          .add("kw-visible");
      }
    });
  },
  { threshold: 0.1 }
);

document.querySelectorAll(
  ".kw-animate"
).forEach(el => observer.observe(el));</pre>
            </div>
            <div class="col-md-4">
                <h4>{l s='PrestaShop JS API' mod='kw_cmscustomjs'}</h4>
                <pre class="kwjs-code-block">// Dostępne obiekty globalne:
// prestashop — główny obiekt PS
// $ / jQuery — jQuery library

// Nasłuchiwanie eventów PS
if (typeof prestashop !== "undefined") {
  prestashop.on(
    "updateCart",
    function(e) {
      console.log("Cart updated", e);
    }
  );
}</pre>
            </div>
        </div>
    </div>
</div>

{* ======================================================================
   STOPKA Z WERSJĄ
   ====================================================================== *}
<div class="panel-footer text-center text-muted kwjs-module-footer">
    <small>
        <strong>KW CMS Custom JS</strong> v{$kw_module_version|escape:'htmlall':'UTF-8'}
        &middot; KEDAR-WIHA.pl
        &middot; PrestaShop 9.0.3 + Optima 3.3.0
    </small>
</div>
