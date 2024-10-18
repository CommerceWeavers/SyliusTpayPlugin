import './img/hourglass.gif';
import './scss/style.scss';
import './js/_pay_by_link';
import './js/apple_pay';
import {CardForm} from "./js/card_form";

document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('[name="sylius_checkout_complete"]')) {
    new CardForm('[name="sylius_checkout_complete"]');
  }
});
