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

// noinspection JSUnresolvedReference

$(document).ready(function () {
    handleDpdShippingPudo();
    $(document).on('click', '.delivery_option_radio', handleDpdShippingPudo);
    $(document).on('click', 'input[name^="delivery_option"]', handleDpdShippingPudo);

    $('.dpdshipping-pudo-container').parent().css('border-right', '0');
    $('.dpdshipping-pudo-cod-container').parent().css('border-right', '0');
    $('.dpdshipping-pudo-swipbox-container').parent().css('border-right', '0');

    $(document).on('click', '.dpdshipping-pudo-open-map-btn', (e) => showModal(e, '#dpdshippingPudoModal'));
    $(document).on('click', '.dpdshipping-pudo-change-map-btn', (e) => showModal(e, '#dpdshippingPudoModal'));

    $(document).on('click', '.dpdshipping-pudo-cod-open-map-btn', (e) => showModal(e, '#dpdshippingPudoCodModal'));
    $(document).on('click', '.dpdshipping-pudo-cod-change-map-btn', (e) => showModal(e, '#dpdshippingPudoCodModal'));

    $(document).on('click', '.dpdshipping-pudo-swipbox-open-map-btn', (e) => showModal(e, '#dpdshippingPudoSwipBoxModal'));
    $(document).on('click', '.dpdshipping-pudo-swipbox-change-map-btn', (e) => showModal(e, '#dpdshippingPudoSwipBoxModal'));
});

function dpdshippingSavePudoCode(pudoCode, modal) {
    $.ajax({
        url: dpdshipping_pickup_save_point_ajax_url,
        type: 'POST',
        data: {
            dpdshipping_token: dpdshipping_token,
            dpdshipping_csrf: dpdshipping_csrf,
            dpdshipping_id_cart: dpdshipping_id_cart,
            dpdshipping_pudo_code: pudoCode
        },
        success: function (response) {
            const resultJson = JSON.parse(response)
            if (resultJson.success) {
                $('.dpdshipping-pudo-new-point').css("display", "none");
                $('.dpdshipping-pudo-selected-point').css("display", "block");
                dpdshippingEnableOrderProcessBtn();
            } else {
                $('.container_dpdshipping_pudo_error').css("display", "block");
                dpdshippingDisableOrderProcessBtn();
                console.error('Error:', response);
            }
            hideModal(modal);
        },
        error: function (error) {
            console.log('Error:', error);
            hideModal(modal);
        }
    });

    function hideModal(modal) {
        setTimeout(() => {
            modal.modal('toggle');
            $(".modal-backdrop").hide();
        }, 500);
    }

}

function dpdshippingGetPudoAddress(pudoCode, input) {
    $.ajax({
        url: dpdshipping_pickup_get_address_ajax_url,
        type: 'GET',
        data: {
            dpdshipping_token: dpdshipping_token,
            dpdshipping_csrf: dpdshipping_csrf,
            dpdshipping_pudo_code: pudoCode
        },
        success: function (response) {
            const resultJson = JSON.parse(response)
            if (resultJson.success && resultJson.data)
                input.text(resultJson.data);
            else
                console.log('Error:', response);
        },
        error: function (error) {
            console.log('Error:', error);
        }
    });
}

function showModal(a, b) {
    let el;
    if (a instanceof Event) {
        a.preventDefault(); a.stopPropagation();
        el = typeof b === 'string' ? document.querySelector(b) : b;
    } else {
        el = typeof a === 'string' ? document.querySelector(a) : a;
    }

    if (!(el instanceof Element)) {
        return;
    }

    if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static', keyboard: false }).show();
    }
    else if (window.jQuery && jQuery.fn && jQuery.fn.modal &&
        jQuery.fn.modal.Constructor && /^4\./.test(String(jQuery.fn.modal.Constructor.VERSION || ''))) {
        jQuery(el).modal({ backdrop: 'static', keyboard: false });
    }
    else {
        el.style.display = 'block';
        el.classList.add('show');
        document.body.classList.add('modal-open');
    }

    if (typeof handleDpdShippingPudo === 'function')
        handleDpdShippingPudo();
}

function handleDpdShippingPudo() {

    $('.container_dpdshipping_pudo_cod_error').css("display", "none");
    $('.container_dpdshipping_pudo_cod_warning').css("display", "none");

    if (getDpdshippingSelectedCarrier() === getDpdshippingIdPudoCarrier()) {
        $('.dpdshipping-pudo-new-point').css("display", "block");
        $('.dpdshipping-pudo-selected-point').css("display", "none");

        $('.dpdshipping-selected-point').text("");
        const dpdShippingWidgetPudoIframe = $("#dpdshiping-widget-pudo-iframe")
        dpdShippingWidgetPudoIframe.attr("src", dpdShippingWidgetPudoIframe.attr("src"));

        dpdshippingDisableOrderProcessBtn();
    } else if (getDpdshippingSelectedCarrier() === getDpdshippingIdPudoCodCarrier()) {
        $('.dpdshipping-pudo-cod-new-point').css("display", "block");
        $('.dpdshipping-pudo-cod-selected-point').css("display", "none");

        $('.dpdshipping-cod-selected-point').text("");
        const dpdShippingWidgetPudoCodIframe = $("#dpdshipping-widget-pudo-cod-iframe")
        dpdShippingWidgetPudoCodIframe.attr("src", dpdShippingWidgetPudoCodIframe.attr("src"));

        dpdshippingDisableOrderProcessBtn();
    }  else if (getDpdshippingSelectedCarrier() === getDpdshippingIdPudoSwipBoxCarrier()) {
        $('.dpdshipping-pudo-swipbox-new-point').css("display", "block");
        $('.dpdshipping-pudo-swipbox-selected-point').css("display", "none");

        $('.dpdshipping-swipbox-selected-point').text("");
        const dpdShippingWidgetPudoSwipboxIframe = $("#dpdshipping-widget-pudo-swipbox-iframe")
        dpdShippingWidgetPudoSwipboxIframe.attr("src", dpdShippingWidgetPudoSwipboxIframe.attr("src"));

        dpdshippingDisableOrderProcessBtn();
    } else {
        dpdshippingEnableOrderProcessBtn();
    }
}

function getDpdshippingIdPudoCarrier() {
    return Number(dpdshipping_id_pudo_carrier);
}

function getDpdshippingIdPudoCodCarrier() {
    return Number(dpdshipping_id_pudo_cod_carrier);
}

function getDpdshippingIdPudoSwipBoxCarrier() {
    return Number(dpdshipping_id_pudo_swipbox_carrier);
}

function getDpdshippingSelectedCarrier() {
    let idSelectedCarrier = $('input[name^="delivery_option"]:checked').val();

    if (typeof idSelectedCarrier == 'undefined')
        return null;

    idSelectedCarrier = idSelectedCarrier.replace(',', '');
    if (typeof idSelectedCarrier == 'undefined' || idSelectedCarrier === 0)
        return null;

    return Number(idSelectedCarrier);
}