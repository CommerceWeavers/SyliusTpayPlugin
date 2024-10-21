import './img/hourglass.gif';
import './scss/style.scss';
import './js/_pay_by_link';
import './js/_visa_mobile';
import {CardForm} from "./js/card_form";

document.addEventListener('DOMContentLoaded', () => {
  if (!document.querySelector('[name="sylius_checkout_complete"]').querySelector('[data-tpay-card-holder-name]')) {
    return;
  }
  new CardForm('[name="sylius_checkout_complete"]');
});
