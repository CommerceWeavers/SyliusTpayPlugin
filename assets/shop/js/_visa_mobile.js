document.addEventListener('DOMContentLoaded', () => {
  const MOBILE_PHONE_MIN_LENGTH = 7;
  const MOBILE_PHONE_MAX_LENGTH = 15;

  let form = document.querySelector('[name="sylius_checkout_complete"]');
  let phoneNumber = form.querySelector('#sylius_checkout_complete_tpay_visa_mobile_phone_number');

  if (null === phoneNumber) {
    return;
  }

  form.addEventListener('submit', (event) => {
    validateVisaMobilePhoneNumber(phoneNumber);

    const isValid = form.querySelectorAll('.sylius-validation-error').length === 0;

    if (!isValid) {
      event.preventDefault();
      event.stopPropagation();

      form.classList.remove('loading');

      return;
    }


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
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = '';
  }

  function addError(field, validationErrorName = 'validationErrorRequired') {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = createErrorElement(field, validationErrorName);
  }

  function createErrorElement(field, validationErrorName = 'validationErrorRequired') {
    const errorMessage = field.dataset[validationErrorName];

    return `
    <div class="ui red pointing label sylius-validation-error">
      ${errorMessage}
    </div>
    `;
  }
});
