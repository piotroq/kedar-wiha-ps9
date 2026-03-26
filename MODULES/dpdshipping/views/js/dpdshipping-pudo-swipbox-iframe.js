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

if (typeof dpsShippingSwipBoxEventCreated == 'undefined') {
    var dpsShippingSwipBoxEventCreated = false;
}

var dpsShippingSwipBox_iframe = document.createElement("iframe");
dpsShippingSwipBox_iframe.setAttribute("id", "dpdshipping-widget-pudo-swipbox-iframe");
dpsShippingSwipBox_iframe.setAttribute("allow", "geolocation");
dpsShippingSwipBox_iframe.src = dpdshipping_iframe_swipbox_url;
dpsShippingSwipBox_iframe.style.width = "100%";
dpsShippingSwipBox_iframe.style.border = "none";
dpsShippingSwipBox_iframe.style.minHeight = "400px";
dpsShippingSwipBox_iframe.style.height = "768px";

var dpsShippingSwipBox_script = document.getElementById("dpdshipping-widget-pudo-swipbox");
if (dpsShippingSwipBox_script)
    dpsShippingSwipBox_script.parentNode.insertBefore(dpsShippingSwipBox_iframe, dpsShippingSwipBox_script);

if (!dpsShippingSwipBoxEventCreated) {
    var dpsShippingSwipBox_eventListener = window[window.addEventListener ? "addEventListener" : "attachEvent"];
    var dpsShippingSwipBox_messageEvent = ("attachEvent" === dpsShippingSwipBox_eventListener) ? "onmessage" : "message";
    dpsShippingSwipBox_eventListener(dpsShippingSwipBox_messageEvent, function (a) {
        if (getDpdshippingIdPudoSwipBoxCarrier() === getDpdshippingSelectedCarrier()) {
            if (a.data.height && !isNaN(a.data.height)) {
                dpsShippingSwipBox_iframe.style.height = a.data.height + "px"
            } else if (a.data.point_id)
                dpdShippingSwipBoxPointSelected(a.data.point_id);
        }
    }, !1);
    dpsShippingSwipBoxEventCreated = true
}

function dpdShippingSwipBoxPointSelected(pudoSwipBoxe) {
    $('.container_dpdshipping_pudo_swipbox_error').css("display", "none");
    $('.dpdshipping-pudo-swipbox-new-point').css("display", "none");
    $('.dpdshipping-pudo-swipbox-selected-point').css("display", "block");

    dpdshippingSavePudoCode(pudoSwipBoxe, $('#dpdshippingPudoSwipBoxModal'));
    dpdshippingGetPudoAddress(pudoSwipBoxe, $('.dpdshipping-swipbox-selected-point'))
}

