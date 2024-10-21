import './js/retry_payment';
import './scss/style.scss';
import './js/_pay_by_link';
import {CardForm} from "./js/card_form";

document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('[name="sylius_checkout_select_payment"]')) {
    new CardForm('[name="sylius_checkout_select_payment"]');
  }
});
