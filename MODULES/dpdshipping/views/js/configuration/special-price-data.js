/*
 * Copyright 2025 DPD Polska Sp. z o.o.
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

$(() => {
    const $importFileInput = $('#dpdSpecialPriceImportFile');
    const $selectedFileName = $('#selectedFileName');
    const $exportButton = $('#dpdSpecialPriceExportButton');
    const $importButton = $('#dpdSpecialPriceImportFileButton');

    $importFileInput.on('change', function () {
        const fileName = $(this).val().split('\\').pop();
        $selectedFileName.text(fileName ? fileName : 'No file chosen');
    });

    $exportButton.on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: dpdshipping_special_price_export_ajax_url,
            method: 'GET',
            data: {
                dpdshipping_token: dpdshipping_token
            },
            xhrFields: {
                responseType: 'blob',
            },
            success: function (data) {
                const url = window.URL.createObjectURL(data);
                const a = $('<a />', {
                    href: url,
                    download: 'export-dpd-ceny-specjalne-prestashop.csv',
                }).appendTo('body');
                a[0].click();
                window.URL.revokeObjectURL(url);
                a.remove();
            },
            error: function () {
                alert('Error exporting the file.');
            },
        });
    });

    $importButton.on('click', function (e) {
        e.preventDefault();
        const file = $importFileInput[0].files[0];
        if (file) {
            const formData = new FormData();
            formData.append('csvFile', file);
            formData.append('dpdshipping_token', dpdshipping_token);

            $.ajax({
                url: dpdshipping_special_price_import_ajax_url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Import failed: ' + response.message);
                    }
                },
                error: function () {
                    alert('Error importing the file.');
                },
            });
        } else {
            alert('Please select a file first.');
        }
    });

    const rowsPerPage = 50;

    function showPage(page) {

        const $tbody = $('#specialPriceTable').find('tbody');
        const $rows = $tbody.find('tr');
        const totalRows = $rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;

        $rows.hide();

        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        $rows.slice(start, end).show();

        $('.pagination-button').removeClass('active');
        $(`.pagination-button[data-page='${page}']`).addClass('active');

        updatePaginationInfo(page, totalPages, rowsPerPage, totalRows);
    }

    function updatePaginationInfo(page, totalPages, rowsPerPage, totalRows) {
        const startRow = (page - 1) * rowsPerPage + 1;
        let endRow = page * rowsPerPage;
        if (endRow > totalRows) endRow = totalRows;

        $('#pagination-info-rows-from').text(startRow);
        $('#pagination-info-rows-to').text(endRow);
        $('#pagination-info-pages').text(totalRows);
    }

    function createPagination() {
        const $pagination = $('#dpdSpecialPricePagination');
        $pagination.empty();

        const $tbody = $('#specialPriceTable').find('tbody');
        const $rows = $tbody.find('tr');
        const totalRows = $rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        const $prev = $('<a href="#" class="pagination-button">Prev</a>');
        if (currentPage === 1) {
            $prev.addClass('disabled');
        }
        $pagination.append($prev);

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage >= totalPages - 2) {
            startPage = Math.max(totalPages - 4, 1);
        }

        if (startPage > 1) {
            const $first = $(`<a href="#" class="pagination-button" data-page="1">1</a>`);
            $pagination.append($first);
            if (startPage > 2) {
                $pagination.append('<span>...</span>');
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const $btn = $(`<a href="#" class="pagination-button" data-page="${i}">${i}</a>`);
            if (i === currentPage) $btn.addClass('active');
            $pagination.append($btn);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                $pagination.append('<span>...</span>');
            }
            const $last = $(`<a href="#" class="pagination-button" data-page="${totalPages}">${totalPages}</a>`);
            $pagination.append($last);
        }

        const $next = $('<a href="#" class="pagination-button">Next</a>');
        if (currentPage === totalPages) {
            $next.addClass('disabled');
        }
        $pagination.append($next);

        if (currentPage === endPage) {
            $('#addFidRow').show();
        } else {
            $('#addFidRow').hide();
        }
    }

    let currentPage = 1;

    showPage(currentPage);

    createPagination();

    $('#dpdSpecialPricePagination').on('click', '.pagination-button', function (e) {
        e.preventDefault();

        if ($(this).hasClass('disabled') || $(this).hasClass('active')) {
            return;
        }

        const $tbody = $('#specialPriceTable').find('tbody');
        const $rows = $tbody.find('tr');
        const totalRows = $rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        const buttonText = $(this).text();

        if (buttonText === 'Prev') {
            if (currentPage > 1) {
                currentPage--;
            }
        } else if (buttonText === 'Next') {
            if (currentPage < totalPages) {
                currentPage++;
            }
        } else {
            currentPage = parseInt($(this).attr('data-page'));
        }

        showPage(currentPage);
        createPagination();
    });
});