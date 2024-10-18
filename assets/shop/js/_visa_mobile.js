document.addEventListener('DOMContentLoaded', () => {
  const COUNTRY_CODED_MOBILE_PHONE_LENGTH = 11;

  let form = document.querySelector('[name="sylius_checkout_complete"]');
  let phoneNumber = form.querySelector('[data-visa-mobile-phone-number]');

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

      if (fieldLength === COUNTRY_CODED_MOBILE_PHONE_LENGTH) {
        clearErrors(phoneNumber);
        return;
      }

      if (fieldLength === 0) {
        addError(phoneNumber, 'validationErrorRequired');
        return;
      }

      addError(phoneNumber);
  }

  function clearErrors(field) {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = '';
  }

  function addError(field, validationErrorName = 'validationErrorLength') {
    const tpayField = field.closest('[data-tpay-field]');
    const errorContainer = tpayField.querySelector('[data-tpay-error-container]');

    errorContainer.innerHTML = createErrorElement(field, validationErrorName);
  }

  function createErrorElement(field, validationErrorName = 'validationErrorLength') {
    const errorMessage = field.dataset[validationErrorName];

    return `
    <div class="ui red pointing label sylius-validation-error">
      ${errorMessage}
    </div>
    `;
  }
});
