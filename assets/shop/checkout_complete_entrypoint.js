import './img/hourglass.gif';
import './scss/style.scss';
import './js/_pay_by_link';
import {CardForm} from "./js/card_form";

document.addEventListener('DOMContentLoaded', () => {
  new CardForm('[name="sylius_checkout_complete"]');
});
