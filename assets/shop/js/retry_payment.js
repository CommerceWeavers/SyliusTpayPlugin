function hideAllPaymentDetails() {
  document.querySelectorAll('[data-payment-details]').forEach((element) => element.style.display = 'none');
  document.querySelectorAll('[data-payment-details] input, [data-payment-details] select, [data-payment-details] textarea, [data-payment-details] button')
    .forEach(function(element) {
      element.disabled = true;
    });
}

function showPaymentDetails(paymentCode) {
  document.querySelectorAll(`[data-payment-details="${paymentCode}"]`).forEach((element) => element.style.display = 'block');
  document.querySelectorAll(`[data-payment-details="${paymentCode}"] input, [data-payment-details="${paymentCode}"] select, [data-payment-details="${paymentCode}"] textarea, [data-payment-details="${paymentCode}"] button`)
    .forEach(function(element) {
      element.disabled = false;
    });
}

function resetPaymentDetails() {
  hideAllPaymentDetails();

  const form = document.querySelector('[name="sylius_checkout_select_payment"]');
  const checkedPaymentCode = form.querySelector('input[type="radio"]:checked').value;

  showPaymentDetails(checkedPaymentCode);

}

document.addEventListener('DOMContentLoaded', () => {
  resetPaymentDetails();

  const form = document.querySelector('[name="sylius_checkout_select_payment"]');

  form.querySelectorAll('input[type="radio"]').forEach((element) => {
    element.addEventListener('change', (event) => {
      hideAllPaymentDetails();

      const form = document.querySelector('[name="sylius_checkout_select_payment"]');
      const checkedPaymentCode = form.querySelector('input[type="radio"]:checked').value;

      showPaymentDetails(checkedPaymentCode);
    })
  })
});
