{**
 * Insert Code HTML to HEAD/FOOTER — Admin Configuration Template
 *
 * v1.1.4: Diagnostic panel now displays a truncated SQL preview
 *         alongside the MySQL error message for full debug visibility
 *         when a save fails. This allows pinpointing exactly which
 *         SQL statement reached the database.
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2024-2026 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 *}

<div class="panel" id="ichf-config">
    <div class="panel-heading">
        <i class="icon-code"></i>
        {$ichf_module_name|escape:'htmlall':'UTF-8'}
        <span class="badge badge-info">v{$ichf_module_version|escape:'htmlall':'UTF-8'}</span>
    </div>

    <div class="alert alert-info">
        <h4><i class="icon-info-circle"></i> {l s='How to use this module' mod='insertcodeheadfooter'}</h4>
        <p>{l s='Paste your custom HTML, JavaScript, or CSS code into the fields below. The code will be injected on every frontend page of your store.' mod='insertcodeheadfooter'}</p>
        <ul>
            <li><strong>{l s='HEAD Code' mod='insertcodeheadfooter'}</strong> — {l s='Injected inside the <head> section (ideal for meta tags, analytics, fonts, schema.org JSON-LD).' mod='insertcodeheadfooter'}</li>
            <li><strong>{l s='FOOTER Code' mod='insertcodeheadfooter'}</strong> — {l s='Injected just before the closing </body> tag (ideal for tracking scripts, chat widgets, deferred JS).' mod='insertcodeheadfooter'}</li>
        </ul>
        <p class="text-muted" style="margin-bottom:0">
            <i class="icon-shield"></i>
            <strong>{l s='WAF-safe transport:' mod='insertcodeheadfooter'}</strong>
            {l s='Values are base64-encoded before submission and persisted via Db::execute with canonical single-quoted SQL — no LIMIT clauses, no Doctrine wrapper edge cases.' mod='insertcodeheadfooter'}
        </p>
    </div>

    {if $ichf_has_diagnostics}
        {* Verify-after-save diagnostic panel *}
        <div class="alert alert-warning" id="ichf-diagnostics">
            <h4><i class="icon-bug"></i> {l s='Last save diagnostics (verify-after-save round trip)' mod='insertcodeheadfooter'}</h4>
            <table class="table table-condensed" style="margin-bottom:0; background:transparent">
                <thead>
                    <tr>
                        <th>{l s='Field' mod='insertcodeheadfooter'}</th>
                        <th>{l s='Transport' mod='insertcodeheadfooter'}</th>
                        <th>{l s='Input bytes' mod='insertcodeheadfooter'}</th>
                        <th>{l s='DB bytes (re-read)' mod='insertcodeheadfooter'}</th>
                        <th>{l s='SQL method' mod='insertcodeheadfooter'}</th>
                        <th>{l s='Status' mod='insertcodeheadfooter'}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>HEAD</strong></td>
                        <td>
                            <code>{$ichf_diagnostics.transport_head|escape:'htmlall':'UTF-8'}</code>
                            {if $ichf_diagnostics.transport_head == 'base64'}
                                <span class="label label-success">{l s='WAF-safe' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.transport_head == 'raw'}
                                <span class="label label-warning">{l s='NoScript' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.transport_head == 'empty'}
                                <span class="label label-default">{l s='No value' mod='insertcodeheadfooter'}</span>
                            {/if}
                        </td>
                        <td><code>{$ichf_diagnostics.head_input_bytes|intval}</code></td>
                        <td><code>{$ichf_diagnostics.head_db_bytes|intval}</code></td>
                        <td><code>{$ichf_diagnostics.head_save_method|escape:'htmlall':'UTF-8'}</code></td>
                        <td>
                            {if $ichf_diagnostics.head_save_ok && $ichf_diagnostics.head_input_bytes == $ichf_diagnostics.head_db_bytes}
                                <span class="label label-success"><i class="icon-check"></i> {l s='Persisted OK' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.head_save_ok}
                                <span class="label label-danger">{l s='MISMATCH' mod='insertcodeheadfooter'}</span>
                            {else}
                                <span class="label label-danger">{l s='SAVE FAILED' mod='insertcodeheadfooter'}</span>
                            {/if}
                        </td>
                    </tr>
                    {if $ichf_diagnostics.head_save_error}
                        <tr>
                            <td colspan="6" style="color:#a94442; background:#f9eaea">
                                <strong>{l s='HEAD MySQL error:' mod='insertcodeheadfooter'}</strong>
                                <code>{$ichf_diagnostics.head_save_error|escape:'htmlall':'UTF-8'}</code>
                            </td>
                        </tr>
                        {if $ichf_diagnostics.head_sql_preview}
                            <tr>
                                <td colspan="6" style="color:#666; background:#fafafa; font-size:11px">
                                    <strong>{l s='HEAD SQL preview:' mod='insertcodeheadfooter'}</strong>
                                    <pre style="margin:4px 0 0 0; padding:6px; background:#fff; border:1px solid #ddd; max-height:120px; overflow:auto">{$ichf_diagnostics.head_sql_preview|escape:'htmlall':'UTF-8'}</pre>
                                </td>
                            </tr>
                        {/if}
                    {/if}
                    <tr>
                        <td><strong>FOOTER</strong></td>
                        <td>
                            <code>{$ichf_diagnostics.transport_footer|escape:'htmlall':'UTF-8'}</code>
                            {if $ichf_diagnostics.transport_footer == 'base64'}
                                <span class="label label-success">{l s='WAF-safe' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.transport_footer == 'raw'}
                                <span class="label label-warning">{l s='NoScript' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.transport_footer == 'empty'}
                                <span class="label label-default">{l s='No value' mod='insertcodeheadfooter'}</span>
                            {/if}
                        </td>
                        <td><code>{$ichf_diagnostics.footer_input_bytes|intval}</code></td>
                        <td><code>{$ichf_diagnostics.footer_db_bytes|intval}</code></td>
                        <td><code>{$ichf_diagnostics.footer_save_method|escape:'htmlall':'UTF-8'}</code></td>
                        <td>
                            {if $ichf_diagnostics.footer_save_ok && $ichf_diagnostics.footer_input_bytes == $ichf_diagnostics.footer_db_bytes}
                                <span class="label label-success"><i class="icon-check"></i> {l s='Persisted OK' mod='insertcodeheadfooter'}</span>
                            {elseif $ichf_diagnostics.footer_save_ok}
                                <span class="label label-danger">{l s='MISMATCH' mod='insertcodeheadfooter'}</span>
                            {else}
                                <span class="label label-danger">{l s='SAVE FAILED' mod='insertcodeheadfooter'}</span>
                            {/if}
                        </td>
                    </tr>
                    {if $ichf_diagnostics.footer_save_error}
                        <tr>
                            <td colspan="6" style="color:#a94442; background:#f9eaea">
                                <strong>{l s='FOOTER MySQL error:' mod='insertcodeheadfooter'}</strong>
                                <code>{$ichf_diagnostics.footer_save_error|escape:'htmlall':'UTF-8'}</code>
                            </td>
                        </tr>
                        {if $ichf_diagnostics.footer_sql_preview}
                            <tr>
                                <td colspan="6" style="color:#666; background:#fafafa; font-size:11px">
                                    <strong>{l s='FOOTER SQL preview:' mod='insertcodeheadfooter'}</strong>
                                    <pre style="margin:4px 0 0 0; padding:6px; background:#fff; border:1px solid #ddd; max-height:120px; overflow:auto">{$ichf_diagnostics.footer_sql_preview|escape:'htmlall':'UTF-8'}</pre>
                                </td>
                            </tr>
                        {/if}
                    {/if}
                </tbody>
            </table>
            <p style="margin:8px 0 0 0; font-size:12px; color:#888">
                <strong>{l s='POST keys received:' mod='insertcodeheadfooter'}</strong>
                {foreach from=$ichf_diagnostics.post_keys item=k name=pk}
                    <code>{$k|escape:'htmlall':'UTF-8'}</code>{if !$smarty.foreach.pk.last}, {/if}
                {/foreach}
            </p>
        </div>
    {/if}

    <form id="ichf-form" method="post" action="{$ichf_action|escape:'htmlall':'UTF-8'}" class="form-horizontal" autocomplete="off">
        {* CSRF token *}
        <input type="hidden" name="ichf_admin_token" value="{$ichf_token|escape:'htmlall':'UTF-8'}" />

        {* Base64-encoded transport fields (populated by JS on submit) *}
        <input type="hidden" name="ichf_h_b64" id="ichf_h_b64" value="{$ichf_head_value_b64|escape:'htmlall':'UTF-8'}" />
        <input type="hidden" name="ichf_f_b64" id="ichf_f_b64" value="{$ichf_footer_value_b64|escape:'htmlall':'UTF-8'}" />

        {* HEAD Code textarea *}
        <div class="form-group">
            <label class="control-label col-lg-2" for="ichf_h_value">
                <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{l s='Code injected in the <head> section of every page. Rendered via displayHeader hook.' mod='insertcodeheadfooter'}">
                    {l s='HEAD Code' mod='insertcodeheadfooter'}
                </span>
            </label>
            <div class="col-lg-10">
                <textarea name="ichf_h_value" id="ichf_h_value" rows="12" class="form-control ichf-code-editor" spellcheck="false" autocomplete="off">{$ichf_head_value|escape:'htmlall':'UTF-8'}</textarea>
                <p class="help-block">
                    {l s='Example: Google Analytics snippet, custom meta tags, external CSS links (fonts.googleapis.com), Schema.org JSON-LD.' mod='insertcodeheadfooter'}
                </p>
            </div>
        </div>

        {* FOOTER Code textarea *}
        <div class="form-group">
            <label class="control-label col-lg-2" for="ichf_f_value">
                <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{l s='Code injected before the closing </body> tag on every page. Rendered via displayBeforeBodyClosingTag hook.' mod='insertcodeheadfooter'}">
                    {l s='FOOTER Code' mod='insertcodeheadfooter'}
                </span>
            </label>
            <div class="col-lg-10">
                <textarea name="ichf_f_value" id="ichf_f_value" rows="12" class="form-control ichf-code-editor" spellcheck="false" autocomplete="off">{$ichf_footer_value|escape:'htmlall':'UTF-8'}</textarea>
                <p class="help-block">
                    {l s='Example: Facebook Pixel, live chat widget, Font Awesome kit, deferred tracking scripts.' mod='insertcodeheadfooter'}
                </p>
            </div>
        </div>

        {* Action buttons *}
        <div class="panel-footer">
            <button type="submit" name="ichf_save" value="1" class="btn btn-default pull-right" id="ichf-save-btn" title="{l s='Save' mod='insertcodeheadfooter'}">
                <i class="process-icon-save"></i> {l s='Save' mod='insertcodeheadfooter'}
            </button>
            <button type="submit" name="ichf_clear_cache" value="1" class="btn btn-default" id="ichf-clear-btn" formnovalidate title="{l s='Clear Cache' mod='insertcodeheadfooter'}">
                <i class="process-icon-refresh"></i> {l s='Clear Cache' mod='insertcodeheadfooter'}
            </button>
        </div>
    </form>
</div>

<style>
{literal}
    #ichf-config .ichf-code-editor {
        font-family: "Courier New", Courier, monospace;
        font-size: 13px;
        tab-size: 2;
        white-space: pre;
        overflow-x: auto;
        line-height: 1.45;
    }
    #ichf-config textarea:focus {
        border-color: #8B0000;
        box-shadow: 0 0 0 2px rgba(139,0,0,0.15);
    }
    #ichf-diagnostics table {
        font-size: 12px;
    }
    #ichf-diagnostics table th {
        background: rgba(0,0,0,0.04);
        font-weight: 600;
    }
    #ichf-diagnostics table td,
    #ichf-diagnostics table th {
        border-top: 1px solid rgba(0,0,0,0.08);
        padding: 6px 10px;
        vertical-align: middle;
    }
    #ichf-diagnostics .label {
        font-size: 11px;
    }
    #ichf-diagnostics code {
        word-break: break-word;
        white-space: normal;
    }
    #ichf-diagnostics pre {
        font-family: "Courier New", Courier, monospace;
        font-size: 11px;
        line-height: 1.4;
    }
{/literal}
</style>

<script type="text/javascript">
{literal}
/**
 * ICHF — Client-side base64 encoder.
 *
 * Reads raw textarea values, base64-encodes them (UTF-8 safe), writes
 * them into hidden fields, and clears the raw textareas before form
 * submission so that WAF/ModSecurity cannot inspect the HTML payload.
 * Only applies to the "Save" action — "Clear Cache" skips encoding.
 *
 * Graceful degradation: any failure leaves raw textareas populated and
 * the server falls back to the NoScript path.
 */
(function () {
    'use strict';

    var FORM_ID = 'ichf-form';
    var HEAD_RAW_ID = 'ichf_h_value';
    var FOOTER_RAW_ID = 'ichf_f_value';
    var HEAD_B64_ID = 'ichf_h_b64';
    var FOOTER_B64_ID = 'ichf_f_b64';
    var SAVE_BTN_ID = 'ichf-save-btn';
    var CLEAR_BTN_ID = 'ichf-clear-btn';

    function encodeUtf8Base64(str) {
        if (typeof str !== 'string') {
            return '';
        }
        try {
            return btoa(unescape(encodeURIComponent(str)));
        } catch (e) {
            return '';
        }
    }

    function onReady() {
        var form = document.getElementById(FORM_ID);
        if (!form) {
            return;
        }

        var clickedButton = null;

        var saveBtn = document.getElementById(SAVE_BTN_ID);
        var clearBtn = document.getElementById(CLEAR_BTN_ID);
        if (saveBtn) {
            saveBtn.addEventListener('click', function () { clickedButton = 'save'; });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () { clickedButton = 'clear'; });
        }

        form.addEventListener('submit', function (event) {
            var action = null;
            if (event.submitter && event.submitter.name) {
                if (event.submitter.name === 'ichf_save') {
                    action = 'save';
                } else if (event.submitter.name === 'ichf_clear_cache') {
                    action = 'clear';
                }
            }
            if (!action) {
                action = clickedButton || 'save';
            }

            if (action === 'clear') {
                return true;
            }

            var headTa = document.getElementById(HEAD_RAW_ID);
            var footerTa = document.getElementById(FOOTER_RAW_ID);
            var headB64 = document.getElementById(HEAD_B64_ID);
            var footerB64 = document.getElementById(FOOTER_B64_ID);

            if (!headTa || !footerTa || !headB64 || !footerB64) {
                return true;
            }

            try {
                headB64.value = encodeUtf8Base64(headTa.value || '');
                footerB64.value = encodeUtf8Base64(footerTa.value || '');
                headTa.value = '';
                footerTa.value = '';
            } catch (err) {
                if (window.console && typeof window.console.error === 'function') {
                    window.console.error('ICHF: base64 encoding failed, falling back to raw POST', err);
                }
            }

            return true;
        });

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
            jQuery('#ichf-config [data-toggle="tooltip"]').tooltip();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
{/literal}
</script>
