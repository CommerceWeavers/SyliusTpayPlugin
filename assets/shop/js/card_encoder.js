import * as JSEncrypt from './jsencrypt.min';
import * as Sentry from "@sentry/browser";

Sentry.init({
  dsn: "https://b4aeb8f84d49fdbae7d336d446bf46b6@o4507962673725440.ingest.de.sentry.io/4507962677395536",

  // Alternatively, use `process.env.npm_package_version` for a dynamic release version
  // if your build tool supports it.
  release: "my-project-name@2.3.12",
  integrations: [
    Sentry.browserTracingIntegration(),
    Sentry.replayIntegration(),
    Sentry.captureConsoleIntegration(),
  ],

  // Set tracesSampleRate to 1.0 to capture 100%
  // of transactions for tracing.
  // We recommend adjusting this value in production
  tracesSampleRate: 1.0,

  // Set `tracePropagationTargets` to control for which URLs trace propagation should be enabled
  tracePropagationTargets: ["localhost", /^https:\/\/yourserver\.io\/api/],

  // Capture Replay for 10% of all sessions,
  // plus for 100% of sessions with an error
  replaysSessionSampleRate: 0.1,
  replaysOnErrorSampleRate: 1.0,
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[name="sylius_checkout_complete"]');
  const submit_button = form.querySelector('[type="submit"]');
  const cards_api = document.getElementById('sylius_checkout_complete_tpay_cards_api').value.replace(/\s/g, '');
  const encrypted_card_field = document.getElementById('sylius_checkout_complete_tpay_card_card');

  const encrypt = new JSEncrypt();
  encrypt.setPublicKey(atob(cards_api));

  submit_button.addEventListener('click', (e) => {
    e.preventDefault();

    const card_number = document.getElementById('sylius_checkout_complete_tpay_card_number').value.replace(/\s/g, '');
    const cvc = document.getElementById('sylius_checkout_complete_tpay_card_cvv').value.replace(/\s/g, '');
    const expiration_date_month = document.getElementById('sylius_checkout_complete_tpay_card_expiration_date_month').value.replace(/\s/g, '');
    const expiration_date_year = document.getElementById('sylius_checkout_complete_tpay_card_expiration_date_year').value.replace(/\s/g, '');
    const expiration_date = [expiration_date_month, expiration_date_year].join('/');

    encrypted_card_field.value = encrypt.encrypt([card_number, expiration_date, cvc, document.location.origin].join('|'));

    form.submit();
  })
});
