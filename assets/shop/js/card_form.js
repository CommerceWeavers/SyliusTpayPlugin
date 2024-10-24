import * as JSEncrypt from './jsencrypt.min';

const MAX_CARD_NUMBER_LENGTH = 16;

export class CardForm {
  #form;
  #cardNumber;
  #cardOperatorIcon;
  #cardsApi;
  #cvc;
  #encryptedCard;
  #expirationMonth;
  #expirationYear;
  #submitButton;

  constructor(selector) {
    this.#form = document.querySelector(selector);
    this.#cardNumber = this.#form.querySelector('[data-tpay-card-number]');
    this.#cardOperatorIcon = this.#form.querySelector('[data-tpay-card-operator-icon]');
    this.#cardsApi = this.#form.querySelector('[data-tpay-cards-api]');
    this.#cvc = this.#form.querySelector('[data-tpay-cvc]');
    this.#encryptedCard = this.#form.querySelector('[data-tpay-encrypted-card]');
    this.#expirationMonth = this.#form.querySelector('[data-tpay-expiration-month]');
    this.#expirationYear = this.#form.querySelector('[data-tpay-expiration-year]');
    this.#submitButton = this.#form.querySelector('[type="submit"]');
    this.#registerEvents();
    this.#registerFormatters();
  }

  #registerCvcFormatting() {
    this.#cvc.addEventListener('keyup', (event) => {
      const value = event.target.value.replace(/\s/g, '');

      event.target.value = value.slice(0, 3);
    });
  }

  #registerCardNumberFormatting() {
    this.#cardNumber.addEventListener('keypress', (event) => {
      const value = event.target.value.replace(/\s/g, '');

      if (value.length >= MAX_CARD_NUMBER_LENGTH) {
        event.preventDefault();
        event.stopPropagation();
      }
    });

    this.#cardNumber.addEventListener('keyup', (event) => {
      const currentPositionStart = event.target.selectionStart;
      const currentLength = event.target.value.length;

      const value = event.target.value.replace(/\s/g, '');

      const parts = value.slice(0, MAX_CARD_NUMBER_LENGTH).match(/\d{1,4}/g) || [];

      event.target.value = parts.join(' ');

      const newLength = event.target.value.length;
      let newCursorPosition = currentPositionStart + (newLength - currentLength);

      if (newCursorPosition < 0) {
        newCursorPosition = 0;
      }

      event.target.setSelectionRange(newCursorPosition, newCursorPosition);
    });
  }

  isCvcValid() {
    const regex = new RegExp(/^\d{3}$/);

    return regex.test(this.getCardCvc());
  }

  isCardNumberValid() {
    const regex = new RegExp(`^\\d{${MAX_CARD_NUMBER_LENGTH}}$`);

    return regex.test(this.getCardNumber());
  }

  isExpirationMonthValid() {
    if (this.getExpirationYear() > new Date().getFullYear()) {
      return true;
    }

    return this.getExpirationMonth() >= new Date().getMonth() + 1;
  }

  isExpirationYearValid() {
    return this.getExpirationYear() >= new Date().getFullYear();
  }

  getCardHolderName() {
    return this.#form.querySelector('[data-tpay-card-holder-name]').value.trim();
  }

  getCardNumber() {
    return this.#cardNumber.value.replace(/\s/g, '');
  }

  getCardCvc() {
    return this.#cvc.value.replace(/\s/g, '');
  }

  getExpirationMonth() {
    if (this.#expirationMonth.value === '') {
      return null;
    }

    return parseInt(this.#expirationMonth.value);
  }

  getExpirationYear() {
    if (this.#expirationYear.value === '') {
      return null;
    }

    return parseInt(this.#expirationYear.value);
  }

  getCardExpirationDate() {
    return [this.#expirationMonth.value, this.#expirationYear.value].join('/');
  }

  #isVisible() {
    return 0 !== (this.#cardNumber.offsetHeight
      + this.#cardNumber.offsetHeight
      + this.#cvc.offsetHeight
      + this.#expirationMonth.offsetHeight
      + this.#expirationYear.offsetHeight);
  }

  #updateEncryptedCard() {
    const encrypt = new JSEncrypt();
    encrypt.setPublicKey(atob(this.#cardsApi.value.replace(/\s/g, '')));

    const data = [this.getCardNumber(), this.getCardExpirationDate(), this.getCardCvc(), document.location.origin].join('|');

    this.#encryptedCard.value = encrypt.encrypt(data);
  }

  #registerFormatters() {
    this.#registerCardNumberFormatting();
    this.#registerCvcFormatting();
  }

  #registerEvents() {
    this.#cvc.addEventListener('change', this.#validateCvc.bind(this));
    this.#cardNumber.addEventListener('change', this.#validateCardNumber.bind(this));
    this.#cardNumber.addEventListener('keyup', this.#updateCardOperatorIcon.bind(this));
    this.#expirationMonth.addEventListener('change', this.#validateExpirationDate.bind(this));
    this.#expirationYear.addEventListener('change', this.#validateExpirationDate.bind(this));

    this.#form.addEventListener('submit', (event) => {
      if (!this.#isVisible()) {
        this.#form.submit();
      }

      this.#validateCardNumber();
      this.#validateCvc();
      this.#validateExpirationDate();

      const isValid = this.#form.querySelectorAll('.sylius-validation-error').length === 0;

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();

        this.#form.classList.remove('loading');

        return;
      }

      this.#updateEncryptedCard();

      this.#form.submit();
    });
  }

  #validateCvc() {
    if (this.isCvcValid()) {
      this.#clearErrors(this.#cvc);
      return;
    }

    this.#addError(this.#cvc);
  }

  #validateCardNumber() {
    if (this.isCardNumberValid()) {
      this.#clearErrors(this.#cardNumber);
      return;
    }

    this.#addError(this.#cardNumber);
  }

  #validateExpirationDate() {
    if (this.getExpirationMonth() === null || this.getExpirationYear() === null) {
      return;
    }

    if (this.isExpirationMonthValid() && this.isExpirationYearValid()) {
      this.#clearErrors(this.#expirationMonth);
      this.#clearErrors(this.#expirationYear);

      return;
    }

    this.#addError(this.#expirationMonth);
  }

  #updateCardOperatorIcon() {
    if (this.#isVisaCard()) {
      this.#cardOperatorIcon.classList.add('cc');
      this.#cardOperatorIcon.classList.add('visa');

      return;
    }

    if (this.#isMasterCard()) {
      this.#cardOperatorIcon.classList.add('cc');
      this.#cardOperatorIcon.classList.add('mastercard');

      return;
    }

    this.#cardOperatorIcon.classList.remove('cc');
    this.#cardOperatorIcon.classList.remove('visa');
    this.#cardOperatorIcon.classList.remove('mastercard');
  }

  #isVisaCard() {
    return this.getCardNumber().startsWith('4');
  }

  #isMasterCard() {
    return this.getCardNumber().startsWith('5');
  }

  #addError(field) {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = this.#createErrorElement(field);
  }

  #createErrorElement(field) {
    const errorMessage = field.dataset.validationError;

    return `
    <div class="ui red pointing label sylius-validation-error">
      ${errorMessage}
    </div>
    `;
  }

  #clearErrors(field) {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = '';
  }
}
