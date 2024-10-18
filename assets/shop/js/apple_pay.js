const handleApplePay = async () => {
  if (!PaymentRequest) {
    return;
  }

  if (!document.querySelector('[data-apple-pay-button]')) {
    return;
  }

  const amount = document.querySelector('[data-apple-pay-amount]').value;
  const merchantIdentifier = document.querySelector('[data-apple-pay-merchant-identifier]').value;
  const currency = document.querySelector('[data-apple-pay-currency]').value;
  const channelName = document.querySelector('[data-apple-pay-channel-name]').value;

  try {
    const paymentMethodData = [{
      "supportedMethods": "https://apple.com/apple-pay",
      "data": {
        "version": 3,
        "merchantIdentifier": merchantIdentifier,
        "merchantCapabilities": [
          "supports3DS"
        ],
        "supportedNetworks": [
          "masterCard",
          "visa"
        ],
        "countryCode": "PL"
      }
    }];

    const paymentDetails = {
      "total": {
        "label": channelName,
        "amount": {
          "value": amount,
          "currency": currency
        }
      }
    };

    const paymentOptions = {
      "requestPayerName": false,
      "requestBillingAddress": false,
      "requestPayerEmail": false,
      "requestPayerPhone": false,
      "requestShipping": false,
      "shippingType": "shipping"
    };


    const request = new PaymentRequest(paymentMethodData, paymentDetails, paymentOptions);

    request.onmerchantvalidation = async event => {
      await fetch('/tpay/apple-pay/init', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          domainName: window.location.hostname,
          displayName: channelName,
          validationUrl: event.validationURL,
        })
      }).then(async response => {
        if (response.status === 200) {
          const result = await response.json();

          event.complete(JSON.parse(atob(result.session)));
        }
      });
    };

    const response = await request.show();
    const status = "success";
    await response.complete(status);

    document.querySelector('[data-apple-pay-token-input]').value = btoa(JSON.stringify(response.details.token.paymentData));
    document.querySelector('form').submit();
  } catch (e) {
    console.error(e);
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  const applePayButton = document.querySelector('[data-apple-pay-button]');

  if (null === applePayButton) {
    return;
  }

  applePayButton.addEventListener('click', () => {
    handleApplePay().catch(console.error);
  });
});
