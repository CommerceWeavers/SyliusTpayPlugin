document.addEventListener('DOMContentLoaded', () => {
  const BLIK_CODE_LENGTH = 6;

  let blikCode = document.querySelector('[data-blik-code-input]');

  if (null === blikCode) {
    return;
  }

  blikCode.addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key)) {
      e.preventDefault();
    }

    if (blikCode.value.length + 1 > BLIK_CODE_LENGTH) {
      e.preventDefault();
    }
  });
});
