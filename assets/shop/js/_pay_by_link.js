document.addEventListener("DOMContentLoaded", function() {
  const tiles = document.querySelectorAll('.bank-tile');
  const hiddenInput = document.getElementById('sylius_checkout_complete_tpay_tpay_channel_id') ??
    document.querySelector("[id^='sylius_checkout_select_payment_payments_'][id$='_tpay_tpay_channel_id']");

  tiles.forEach(tile => {
    tile.addEventListener('click', function () {
      tiles.forEach(tileElement => tileElement.classList.remove('selected'));

      tile.classList.add('selected');

      hiddenInput.value = tile.getAttribute('data-bank-id');
    });
  });
});
