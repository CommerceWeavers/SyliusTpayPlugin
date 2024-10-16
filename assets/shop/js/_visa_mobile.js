import 'intl-tel-input/build/css/intlTelInput.css';

import intlTelInput from 'intl-tel-input';

const inputElement = '#sylius_checkout_complete_tpay_visa_mobile_phone_number';

const input = document.querySelector(inputElement);
const locale = input.dataset.locale;

const iti = intlTelInput(input, {
  initialCountry: locale,
  strictMode: true,
  nationalMode: true,
  utilsScript: "/intl-tel-input/js/utils.js?1727952657388",
  loadUtilsOnInit: () => import("intl-tel-input/utils"),
});

document.addEventListener('DOMContentLoaded', () => {
  let form = document.querySelector('[name="sylius_checkout_complete"]');
  let phoneNumber = form.querySelector('[data-visa-mobile-phone-number]');

  form.addEventListener('submit', (event) => {
    validateVisaMobilePhoneNumber();

    const isValid = form.querySelectorAll('.sylius-validation-error').length === 0;

    if (!isValid) {
      event.preventDefault();
      event.stopPropagation();

      form.classList.remove('loading');

      return;
    }

    phoneNumber.value = iti.getNumber().replace(/\D/g, '');

    form.submit();
  });

  function validateVisaMobilePhoneNumber() {
      if (iti.isValidNumber()) {
        clearErrors(phoneNumber);
        return;
      }

      addError(phoneNumber);
  }

  function clearErrors(field) {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = '';
  }

  function addError(field) {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = createErrorElement(field);
  }

  function createErrorElement(field) {
    const errorMessage = field.dataset.validationError;

    return `
    <div class="ui red pointing label sylius-validation-error">
      ${errorMessage}
    </div>
    `;
  }
});
