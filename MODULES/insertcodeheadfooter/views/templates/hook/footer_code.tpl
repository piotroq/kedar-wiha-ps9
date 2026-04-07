{**
 * Insert Code HTML to HEAD/FOOTER — FOOTER Code Hook Template
 *
 * Rendered by displayBeforeBodyClosingTag hook. Outputs raw HTML/JS/CSS
 * just before the closing </body> tag on every frontend page.
 *
 * @author    KEDAR-WIHA.pl
 * @copyright 2024-2026 KEDAR-WIHA.pl
 * @license   Academic Free License 3.0 (AFL-3.0)
 *}
{if isset($ichf_footer) && $ichf_footer}
<!-- InsertCodeHeadFooter: FOOTER -->
{$ichf_footer nofilter}
<!-- /InsertCodeHeadFooter: FOOTER -->
{/if}
