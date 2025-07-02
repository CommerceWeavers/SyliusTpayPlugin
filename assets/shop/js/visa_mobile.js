import 'intl-tel-input/build/css/intlTelInput.css';
import intlTelInput from 'intl-tel-input';

document.addEventListener('DOMContentLoaded', () => {
  const MOBILE_PHONE_MIN_LENGTH = 7;
  const MOBILE_PHONE_MAX_LENGTH = 15;

  let phoneNumber = document.querySelector('[data-visa-mobile-phone-number]');

  if (null === phoneNumber) {
    return;
  }

  let form = phoneNumber.closest('form');

  phoneNumber.addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  });

  const intlPhoneNumber = intlTelInput(phoneNumber, {
    initialCountry: 'pl',
    loadUtilsOnInit: () => import("intl-tel-input/utils"),
    formatAsYouType: false,
  });

  form.addEventListener('submit', (event) => {
    validateVisaMobilePhoneNumber(phoneNumber);

    const isValid = form.querySelectorAll('.invalid-feedback').length === 0;

    if (!isValid) {
      event.preventDefault();
      event.stopPropagation();

      form.classList.remove('loading');

      return;
    }

    phoneNumber.value = intlPhoneNumber.getNumber().substring(1);

    form.submit();
  });

  function validateVisaMobilePhoneNumber(field) {
      let fieldLength = field.value.length;

      if (MOBILE_PHONE_MIN_LENGTH <= fieldLength && fieldLength <= MOBILE_PHONE_MAX_LENGTH) {
        clearErrors(phoneNumber);
        return;
      }

      if (fieldLength === 0) {
        addError(phoneNumber);
        return;
      }

      if (fieldLength < MOBILE_PHONE_MIN_LENGTH) {
        addError(phoneNumber, 'validationErrorMinLength');
        return;
      }

    if (fieldLength > MOBILE_PHONE_MAX_LENGTH) {
      addError(phoneNumber, 'validationErrorMaxLength');
      return;
    }

      addError(phoneNumber);
  }

  function clearErrors(field) {
    const tpayField = field.closest('[data-tpay-field-wrapper]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = '';
  }

  function addError(field, validationErrorName = 'validationErrorRequired') {
    const tpayField = field.closest('[data-tpay-field-wrapper]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = createErrorElement(field, validationErrorName);
  }

  function createErrorElement(field, validationErrorName = 'validationErrorRequired') {
    const errorMessage = field.dataset[validationErrorName];

    return `
    <div class="invalid-feedback d-block">
      ${errorMessage}
    </div>
    `;
  }
});
