/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */(()=>{(()=>{"use strict";const p=a=>{document.readyState==="loading"?document.addEventListener("DOMContentLoaded",a):a()},v={PRZELEWY24_INSTALLMENT_CONFIG:".js-przelewy24-installment-calculator-config",PRZELEWY24_INSTALLMENT_CALCULATOR_MODAL:"calculator-modal",PRZELEWY24_INSTALLMENT_WIDGET_MAX:"installment-widget-max",PRZELEWY24_INSTALLMENT_WIDGET_MAX_SELECTOR:".js-przelewy24-installment-widget-max"};var L=(a,c,e)=>new Promise((i,n)=>{var r=t=>{try{o(e.next(t))}catch(l){n(l)}},s=t=>{try{o(e.throw(t))}catch(l){n(l)}},o=t=>t.done?i(t.value):Promise.resolve(t.value).then(r,s);o((e=e.apply(a,c)).next())});const _=(a,c,e=()=>{})=>L(void 0,null,function*(){if(document.getElementById(a))e();else{const n=document.createElement("script");return n.src=c,n.id=a,document.head.appendChild(n),yield new Promise(r=>{n.addEventListener("load",()=>r(e()))})}return!0});var d=(a,c,e)=>new Promise((i,n)=>{var r=t=>{try{o(e.next(t))}catch(l){n(l)}},s=t=>{try{o(e.throw(t))}catch(l){n(l)}},o=t=>t.done?i(t.value):Promise.resolve(t.value).then(r,s);o((e=e.apply(a,c)).next())});const y=()=>{const{PRZELEWY24_INSTALLMENT_CONFIG:a,PRZELEWY24_INSTALLMENT_CALCULATOR_MODAL:c,PRZELEWY24_INSTALLMENT_WIDGET_MAX:e,PRZELEWY24_INSTALLMENT_WIDGET_MAX_SELECTOR:i}=v,n=()=>{const l=document.querySelector(i);l&&l.addEventListener("click",u=>{u.preventDefault()})},r=()=>d(void 0,null,function*(){var l;const u=document.querySelector(a),m=(l=u==null?void 0:u.dataset)==null?void 0:l.config;if(!m){console.error("No P24 installment calculator config provided");return}const E=new InstallmentCalculatorApp(JSON.parse(m));(yield E.create(c)).render(c),(yield E.create("max-widget")).render(e)}),s=()=>d(void 0,null,function*(){yield _("installment-calculator","https://apm.przelewy24.pl/installments/installment-calculator-app.umd.sdk.js",r)}),o=()=>{prestashop.on("updatedCart",()=>d(void 0,null,function*(){yield r()})),prestashop.on("updatedProduct",()=>d(void 0,null,function*(){yield r(),n()})),n()};return{init:()=>d(void 0,null,function*(){yield s(),o()})}};var C=(a,c,e)=>new Promise((i,n)=>{var r=t=>{try{o(e.next(t))}catch(l){n(l)}},s=t=>{try{o(e.throw(t))}catch(l){n(l)}},o=t=>t.done?i(t.value):Promise.resolve(t.value).then(r,s);o((e=e.apply(a,c)).next())});p(()=>C(void 0,null,function*(){const{init:a}=y();yield a()}))})();})();

//# sourceMappingURL=przelewy24-product.js.map
