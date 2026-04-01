/**
 * KEDAR-WIHA.pl — kw_cmscustomcss :: Admin JavaScript
 * Funkcjonalności panelu konfiguracji: counter, loader, tab key support.
 *
 * @version 1.0.0
 * @requires ES6+
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    /* =========================================================================
       1. CHARACTER COUNTER — Licznik znaków w textarea
       ========================================================================= */

    const textarea = document.getElementById('KW_CMS_CUSTOM_CSS');
    const counter = document.getElementById('kw-css-char-count');
    const maxLength = parseInt(counter?.textContent?.match(/\d+(?:\s*\/\s*(\d+))/)?.[1] || '500000', 10);

    /**
     * Aktualizuje licznik znaków z kolorowym feedbackiem.
     */
    const updateCharCount = () => {
        if (!textarea || !counter) return;

        const length = textarea.value.length;
        const formatted = length.toLocaleString('pl-PL');
        const maxFormatted = maxLength.toLocaleString('pl-PL');

        counter.textContent = `${formatted} / ${maxFormatted} znaków`;

        // Feedback wizualny
        counter.classList.remove('kw-css-warning', 'kw-css-danger');
        if (length > maxLength * 0.9) {
            counter.classList.add('kw-css-danger');
        } else if (length > maxLength * 0.7) {
            counter.classList.add('kw-css-warning');
        }
    };

    if (textarea) {
        textarea.addEventListener('input', updateCharCount);
        textarea.addEventListener('paste', () => setTimeout(updateCharCount, 50));
        // Inicjalny count
        updateCharCount();
    }

    /* =========================================================================
       2. TAB KEY SUPPORT — Wstawianie tabulacji w textarea
       ========================================================================= */

    if (textarea) {
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();

                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;

                // Wstaw 2 spacje (standard CSS indent)
                textarea.value = value.substring(0, start) + '  ' + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + 2;

                // Trigger input event dla countera
                textarea.dispatchEvent(new Event('input'));
            }
        });
    }

    /* =========================================================================
       3. LOAD CSS BUTTON — AJAX ładowanie CSS per CMS page
       ========================================================================= */

    const loadBtn = document.getElementById('kw-load-css-btn');
    const cmsSelect = document.getElementById('KW_CMS_PAGE_ID');

    if (loadBtn && cmsSelect) {
        loadBtn.addEventListener('click', () => {
            const cmsId = parseInt(cmsSelect.value, 10);

            if (!cmsId || cmsId <= 0) {
                // Fallback: wyślij formularz z parametrem edycji
                alert('Wybierz stronę CMS z listy.');
                return;
            }

            // Redirect do tej samej strony z parametrem edit
            const formAction = document.getElementById('kw_cms_custom_css_form')?.action || '';
            if (formAction) {
                window.location.href = `${formAction}&editKwCmsCustomCss=1&id_cms_edit=${cmsId}`;
            }
        });
    }

    /* =========================================================================
       4. FORM VALIDATION — Walidacja przed wysłaniem
       ========================================================================= */

    const form = document.getElementById('kw_cms_custom_css_form');

    if (form) {
        form.addEventListener('submit', (e) => {
            const cmsId = parseInt(cmsSelect?.value || '0', 10);

            if (!cmsId || cmsId <= 0) {
                e.preventDefault();
                alert('Musisz wybrać stronę CMS przed zapisem.');
                cmsSelect?.focus();
                return false;
            }

            // Sprawdź długość
            if (textarea && textarea.value.length > maxLength) {
                e.preventDefault();
                alert(`CSS przekracza maksymalną długość (${maxLength.toLocaleString('pl-PL')} znaków).`);
                textarea.focus();
                return false;
            }

            // Prosta walidacja nawiasów
            if (textarea) {
                const openBraces = (textarea.value.match(/{/g) || []).length;
                const closeBraces = (textarea.value.match(/}/g) || []).length;

                if (openBraces !== closeBraces) {
                    const proceed = confirm(
                        `Uwaga: niezbalansowane nawiasy klamrowe (${openBraces} otwierających vs ${closeBraces} zamykających).\n` +
                        'Czy mimo to chcesz zapisać?'
                    );
                    if (!proceed) {
                        e.preventDefault();
                        textarea.focus();
                        return false;
                    }
                }
            }

            return true;
        });
    }

    /* =========================================================================
       5. COLLAPSIBLE REFERENCE PANEL — Toggle chevron icon
       ========================================================================= */

    const collapsiblePanels = document.querySelectorAll('.kw-collapsible');

    collapsiblePanels.forEach((panel) => {
        const target = document.querySelector(panel.dataset.target);
        const chevron = panel.querySelector('.icon-chevron-down, .icon-chevron-up');

        if (target) {
            // Bootstrap collapse event listeners
            target.addEventListener('show.bs.collapse', () => {
                if (chevron) {
                    chevron.classList.remove('icon-chevron-down');
                    chevron.classList.add('icon-chevron-up');
                }
            });

            target.addEventListener('hide.bs.collapse', () => {
                if (chevron) {
                    chevron.classList.remove('icon-chevron-up');
                    chevron.classList.add('icon-chevron-down');
                }
            });
        }
    });

    /* =========================================================================
       6. AUTO-RESIZE TEXTAREA — Opcjonalne auto-resize przy dużej ilości CSS
       ========================================================================= */

    if (textarea) {
        const autoResize = () => {
            // Tylko rozszerzaj, nie zmniejszaj poniżej 300px
            const minHeight = 300;
            textarea.style.height = 'auto';
            const newHeight = Math.max(textarea.scrollHeight, minHeight);
            textarea.style.height = `${newHeight}px`;
        };

        textarea.addEventListener('input', autoResize);
        // Inicjalny resize
        setTimeout(autoResize, 100);
    }
});
