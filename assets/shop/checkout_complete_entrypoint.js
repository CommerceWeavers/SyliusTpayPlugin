import './img/hourglass.gif';
import './scss/pay_by_link.scss';
import './scss/style.scss';
import './js/_visa_mobile';
import './js/apple_pay';
import './js/blik_code';
import {CardForm} from "./js/card_form";

document.addEventListener('DOMContentLoaded', () => {
  if (!document.querySelector('[name="sylius_checkout_complete"]').querySelector('[data-tpay-card-number]')) {
    return;
  }
  new CardForm('[name="sylius_checkout_complete"]');
});
