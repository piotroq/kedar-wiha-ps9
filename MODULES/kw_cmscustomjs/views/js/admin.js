/**
 * KEDAR-WIHA.pl — kw_cmscustomjs :: Admin JavaScript
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const textarea  = document.getElementById('KW_CMS_CUSTOM_JS');
    const counter   = document.getElementById('kwjs-char-count');
    const cmsSelect = document.getElementById('KW_CMS_PAGE_ID');
    const loadBtn   = document.getElementById('kwjs-load-btn');
    const form      = document.getElementById('kw_cms_custom_js_form');

    // Parse max length from counter text
    const maxLength = parseInt(counter?.textContent?.match(/\d+(?:\s*\/\s*(\d+))/)?.[1] || '500000', 10);

    /* === CHARACTER COUNTER === */
    const updateCharCount = () => {
        if (!textarea || !counter) return;
        const len = textarea.value.length;
        counter.textContent = `${len.toLocaleString('pl-PL')} / ${maxLength.toLocaleString('pl-PL')} znaków`;
        counter.classList.remove('kwjs-warning', 'kwjs-danger');
        if (len > maxLength * 0.9) counter.classList.add('kwjs-danger');
        else if (len > maxLength * 0.7) counter.classList.add('kwjs-warning');
    };

    if (textarea) {
        textarea.addEventListener('input', updateCharCount);
        textarea.addEventListener('paste', () => setTimeout(updateCharCount, 50));
        updateCharCount();
    }

    /* === TAB KEY → 2 spaces === */
    if (textarea) {
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = textarea.selectionStart;
                const end   = textarea.selectionEnd;
                textarea.value = textarea.value.substring(0, start) + '  ' + textarea.value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + 2;
                textarea.dispatchEvent(new Event('input'));
            }
        });
    }

    /* === LOAD JS BUTTON === */
    if (loadBtn && cmsSelect) {
        loadBtn.addEventListener('click', () => {
            const cmsId = parseInt(cmsSelect.value, 10);
            if (!cmsId || cmsId <= 0) {
                alert('Wybierz stronę CMS z listy.');
                return;
            }
            const action = form?.action || '';
            if (action) {
                window.location.href = `${action}&editKwCmsCustomJs=1&id_cms_edit=${cmsId}`;
            }
        });
    }

    /* === FORM VALIDATION === */
    if (form) {
        form.addEventListener('submit', (e) => {
            const cmsId = parseInt(cmsSelect?.value || '0', 10);

            if (!cmsId || cmsId <= 0) {
                e.preventDefault();
                alert('Musisz wybrać stronę CMS przed zapisem.');
                cmsSelect?.focus();
                return false;
            }

            if (textarea && textarea.value.length > maxLength) {
                e.preventDefault();
                alert(`JS przekracza maksymalną długość (${maxLength.toLocaleString('pl-PL')} znaków).`);
                textarea.focus();
                return false;
            }

            // Bracket balance check
            if (textarea) {
                const val = textarea.value;
                const ob = (val.match(/{/g) || []).length;
                const cb = (val.match(/}/g) || []).length;
                const op = (val.match(/\(/g) || []).length;
                const cp = (val.match(/\)/g) || []).length;

                if (ob !== cb || op !== cp) {
                    const msg = [];
                    if (ob !== cb) msg.push(`klamrowe: ${ob} otwierających vs ${cb} zamykających`);
                    if (op !== cp) msg.push(`okrągłe: ${op} otwierających vs ${cp} zamykających`);
                    const proceed = confirm(
                        `Uwaga: niezbalansowane nawiasy:\n${msg.join('\n')}\n\nCzy mimo to chcesz zapisać?`
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

    /* === COLLAPSIBLE REFERENCE === */
    document.querySelectorAll('.kwjs-collapsible').forEach((panel) => {
        const target  = document.querySelector(panel.dataset.target);
        const chevron = panel.querySelector('.icon-chevron-down, .icon-chevron-up');
        if (target) {
            target.addEventListener('show.bs.collapse', () => {
                if (chevron) { chevron.classList.remove('icon-chevron-down'); chevron.classList.add('icon-chevron-up'); }
            });
            target.addEventListener('hide.bs.collapse', () => {
                if (chevron) { chevron.classList.remove('icon-chevron-up'); chevron.classList.add('icon-chevron-down'); }
            });
        }
    });

    /* === AUTO-RESIZE TEXTAREA === */
    if (textarea) {
        const autoResize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = `${Math.max(textarea.scrollHeight, 300)}px`;
        };
        textarea.addEventListener('input', autoResize);
        setTimeout(autoResize, 100);
    }
});
