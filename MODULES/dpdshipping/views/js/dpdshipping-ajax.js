/*
 * Copyright 2024 DPD Polska Sp. z o.o.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the EUPL-1.2 or later.
 * You may not use this work except in compliance with the Licence.
 *
 * You may obtain a copy of the Licence at:
 * https://joinup.ec.europa.eu/software/page/eupl
 * It is also bundled with this package in the file LICENSE.txt
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the Licence is distributed on an AS IS basis,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the Licence for the specific language governing permissions
 * and limitations under the Licence.
 *
 * @author    DPD Polska Sp. z o.o.
 * @copyright 2024 DPD Polska Sp. z o.o.
 * @license   https://joinup.ec.europa.eu/software/page/eupl
 */

$(document).ready(function () {

    function downloadFile(xhr, response) {
        const contentType = xhr.getResponseHeader('Content-Type');
        const fileName = getFileName(xhr);
        const blob = new Blob([response], {type: contentType});
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function getFileName(xhr) {
        const contentDisposition = xhr.getResponseHeader('Content-Disposition');
        let fileName = 'downloaded-file';

        if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(contentDisposition);
            if (matches != null && matches[1]) {
                fileName = matches[1].replace(/['"]/g, '');
            }
        }
        return fileName;
    }

    $('#print-label').on('click', function (event) {
        event.preventDefault();

        const shippingHistoryId = $(this).data('shipping-history-id');
        const url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                shippingHistoryId: shippingHistoryId,
                token: dpdshipping_token
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response, status, xhr) {
                downloadFile(xhr, response);
                $('.alert-messages').hide();
                $('.success-message-ajax').text(dpdshipping_translations.dpdshipping_label_success_text).show();
                $('.error-message-ajax').hide();
            },
            error: function () {
                handleErrorResponse(dpdshipping_translations.dpdshipping_label_error_text);
            }
        });
    });


    $('#print-return-label').on('click', function (event) {
        event.preventDefault();

        const shippingHistoryId = $(this).data('shipping-history-id');
        const orderId = $(this).data('order-id');
        const url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                shippingHistoryId: shippingHistoryId,
                orderId: orderId,
                token: dpdshipping_token
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response, status, xhr) {
                downloadFile(xhr, response);
                $('.alert-messages').hide();
                $('.success-message-ajax').text(dpdshipping_translations.dpdshipping_return_label_success_text).show();
                $('.error-message-ajax').hide();
            },
            error: function () {
                handleErrorResponse(dpdshipping_translations.dpdshipping_return_label_error_text);
            }
        });
    });

    function handleErrorResponse(message) {
        $('.alert-messages').hide();
        $('.success-message-ajax').hide();
        $('.error-message-ajax').text(message).show();
    }

    const $dpdConn  = $('.js-dpd-connection');
    const $dpdPayer = $('.js-dpd-payer');
    if ($dpdConn.length === 0 || $dpdPayer.length === 0) return;

    const dpdOriginalByLabel = Object.create(null);
    let dpdPlaceholder = null;

    $dpdPayer.find('option').each((_, el) => {
        const $opt = $(el);
        const val  = String($opt.attr('value') ?? '');
        const text = $opt.text();
        if (val === '') {
            dpdPlaceholder = { value: '', text: text || '—' };
        } else {
            dpdOriginalByLabel[text] = val;
        }
    });

    let dpdPayersByConn = {};
    try {
        let dpdRaw = $dpdConn.attr('data-payers') || '{}';
        if (dpdRaw.includes('&quot;') || dpdRaw.includes('&#039;') || dpdRaw.includes('&amp;')) {
            dpdRaw = dpdRaw.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, '&');
        }
        dpdPayersByConn = JSON.parse(dpdRaw);
    } catch {
        dpdPayersByConn = {};
    }

    const dpdFillPayers = () => {
        const dpdConnId = String($dpdConn.val() || '');
        const dpdMap    = dpdPayersByConn[dpdConnId] || {};
        const dpdLabels = Object.keys(dpdMap);
        const dpdRemembered = String($dpdPayer.val() || $dpdPayer.attr('data-selected') || '');

        $dpdPayer.empty();

        if (dpdLabels.length === 0) {
            const text = dpdPlaceholder?.text ?? '—';
            $('<option/>', { value: '', text }).appendTo($dpdPayer);
            $dpdPayer.prop('disabled', true);
            return;
        }

        $dpdPayer.prop('disabled', false);

        let dpdSelectedSet = false;

        if (dpdPlaceholder) {
            $('<option/>', { value: '', text: dpdPlaceholder.text }).appendTo($dpdPayer);
        }

        for (const label of dpdLabels) {
            const originalVal = String(dpdOriginalByLabel[label] ?? '');
            const isSelected  = !dpdSelectedSet && originalVal !== '' && originalVal === dpdRemembered;

            $('<option/>', {
                value: originalVal || '',
                text:  label,
                selected: isSelected
            }).appendTo($dpdPayer);

            if (isSelected) dpdSelectedSet = true;
        }

        if (!dpdSelectedSet) {
            const firstIndex = dpdPlaceholder ? 1 : 0;
            const $opts = $dpdPayer.find('option');
            if ($opts.length > firstIndex) $opts.eq(firstIndex).prop('selected', true);
        }

        $dpdPayer.attr('data-selected', '');
    };

    dpdFillPayers();

    $dpdConn.on('change', () => {
        $dpdPayer.attr('data-selected', String($dpdPayer.val() || ''));
        dpdFillPayers();
    });

    $dpdPayer.closest('form').on('submit', () => {
        $dpdPayer.prop('disabled', false);
    });
});