!function(e){var t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={i:r,l:!1,exports:{}};return e[r].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=2)}([,,function(e,t,n){n(3),e.exports=n(10)},function(e,t,n){"use strict";n.r(t);n(4),n(5)},function(e,t){function n(e,t){var n="",r=document.querySelector(".give-save-button");try{n=JSON.parse(t),e.value=JSON.stringify(n,void 0,2),e.style.border="none",r.removeAttribute("disabled")}catch(t){e.style.border="1px solid red",r.setAttribute("disabled","disabled")}}function r(e){null!==e&&(n(e,e.value),e.addEventListener("blur",(function(t){n(e,t.target.value)})))}window.addEventListener("DOMContentLoaded",(function(){var e=document.querySelectorAll(".stripe-checkout-field"),t=document.querySelector(".stripe-cc-field-format-settings"),n=document.getElementById("stripe_checkout_enabled"),i=document.getElementById("stripe_enable_apple_google_pay"),o=document.querySelectorAll('input[name="stripe_fonts"]'),a=document.getElementById("stripe_styles_base"),u=document.getElementById("stripe_styles_empty"),l=document.getElementById("stripe_styles_invalid"),s=document.getElementById("stripe_styles_complete"),c=document.getElementById("stripe_custom_fonts"),d=document.getElementById("give-stripe-add-new-account"),p=document.querySelector(".give-stripe-add-account-errors"),v=Array.from(document.querySelectorAll(".give-stripe-register-domain")),y=Array.from(document.querySelectorAll(".give-stripe-reset-domain"));r(a),r(u),r(l),r(s),r(c),null!==v&&v.forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault();var t=e.target.parentNode.previousElementSibling.parentNode,n=new XMLHttpRequest,r=new FormData;r.append("action","give_stripe_register_domain"),r.append("slug",e.target.getAttribute("data-account")),r.append("secret_key",e.target.getAttribute("data-secret-key")),r.append("account_id",e.target.getAttribute("data-account-id")),r.append("type",e.target.getAttribute("data-type")),n.open("POST",ajaxurl),n.onload=function(){var e=JSON.parse(n.response);200===n.status&&e.success&&(t.querySelector(".give-stripe-account-badge").classList.remove("give-hidden"),t.querySelector(".give-stripe-account-register").classList.add("give-hidden"))},n.send(r)}))})),null!==y&&y.forEach((function(e){e.addEventListener("click",(function(e){e.preventDefault();var t=e.target.parentElement.parentElement.parentElement,n=new XMLHttpRequest,r=new FormData;r.append("action","give_stripe_reset_domain"),n.open("POST",ajaxurl),n.onload=function(){var e=JSON.parse(n.response);200===n.status&&e.success&&(t.querySelector(".give-stripe-account-badge").classList.add("give-hidden"),t.querySelector(".give-stripe-account-register").classList.remove("give-hidden"))},n.send(r)}))})),null!==d&&d.addEventListener("click",(function(e){e.preventDefault();var t=document.querySelector('input[name="test_secret_key"]'),n=document.querySelector('input[name="live_secret_key"]'),r=document.querySelector('input[name="test_publishable_key"]'),i=document.querySelector('input[name="live_publishable_key"]'),o=e.target.getAttribute("data-error");if(0===t.value.trim().length||0===n.value.trim().length||0===r.value.trim().length||0===i.value.trim().length)return p.innerHTML='<div class="give-notice notice error notice-error"><p>'.concat(o,"</p></div>"),!1;d.nextElementSibling.style.visibility="visible",d.nextElementSibling.style.float="none";var a=new XMLHttpRequest,u=new FormData;u.append("action","give_stripe_add_manual_account"),u.append("account_slug",e.target.getAttribute("data-account")),u.append("live_secret_key",n.value.trim()),u.append("test_secret_key",t.value.trim()),u.append("test_publishable_key",r.value.trim()),u.append("live_publishable_key",i.value.trim()),a.open("POST",ajaxurl),a.onload=function(){var t=JSON.parse(a.response);200===a.status&&t.success&&(window.location.href=e.target.getAttribute("data-url"))},a.send(u)})),null!==o&&o.forEach((function(e){var t=document.querySelector(".give-stripe-google-fonts-wrap"),n=document.querySelector(".give-stripe-custom-fonts-wrap");e.addEventListener("change",(function(e){"custom_fonts"===e.target.value?(t.style.display="none",n.style.display="table-row"):"google_fonts"===e.target.value&&(t.style.display="table-row",n.style.display="none")}))})),null!==n&&null!==i&&(e.forEach((function(r,i){n.checked&&(e[i].style.display="table-row",t.style.display="none")})),n.addEventListener("click",(function(){e.forEach((function(r,i){n.checked?(e[i].style.display="table-row",t.style.display="none"):(e[i].style.display="none",t.style.display="table-row")}))})))}))},function(e,t){var n;jQuery.noConflict(),(n=jQuery)((function(){n(".give-stripe-customer-id-update").on("click",(function(e){e.preventDefault(),n(".give-stripe-customer-link").hide(),n(this).hide(),n(".give-stripe-customer-submit-wrap, .give-stripe-customer-id-input").show()})),n(".give-stripe-customer-id-cancel").on("click",(function(e){e.preventDefault(),n(".give-stripe-customer-link, .give-stripe-customer-id-update").show(),n(".give-stripe-customer-submit-wrap, .give-stripe-customer-id-input").hide()}))}))},,,,,function(e,t){}]);