document.addEventListener('DOMContentLoaded', () => {
  const testConnectionButton = document.getElementById('test-connection-button');
  const testConnectionMessage = document.getElementById('test-connection-message');
  let localeCode = '';

  if (testConnectionButton === null || testConnectionMessage === null) {
    return;
  }

  testConnectionButton.addEventListener('click', function() {
    const productionModeElement = document.getElementsByName('sylius_payment_method[gatewayConfig][config][production_mode]')[0];
    const clientIdElement = document.getElementsByName('sylius_payment_method[gatewayConfig][config][client_id]')[0];
    const clientSecretElement = document.getElementsByName('sylius_payment_method[gatewayConfig][config][client_secret]')[0];
    const productionMode = productionModeElement !== undefined ? productionModeElement.value : 'false';
    const clientId = clientIdElement !== undefined ? clientIdElement.value : '';
    const clientSecret = clientSecretElement !== undefined ? clientSecretElement.value : '';

    fetch('/admin/tpay/channels?productionMode=' + productionMode, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Client-Id': clientId,
        'X-Client-Secret': clientSecret,
      },
    })
      .then(response => {
        return response.json().then(jsonResponse => {
          if (!response.ok) {
            throw new Error();
          }

          localeCode = response.headers.get('Accept-Language');

          return jsonResponse;
        });
      })
      .then(data => {
        convertTpayChannelIdInputIntoSelect(data);

        testConnectionMessage.innerText = getNotificationMessage(localeCode, 'success');
        testConnectionMessage.classList.remove('negative');
        testConnectionMessage.classList.add('positive');
      })
      .catch(() => {
        testConnectionMessage.innerText = getNotificationMessage(localeCode, 'error');
        testConnectionMessage.classList.remove('positive');
        testConnectionMessage.classList.add('negative');
      })
  });
});

const NOTIFICATION_MESSAGES = {
  en: {
    connectionTest: {
      success: 'Connection test successful. Channels loaded.',
      error: 'Connection test failed. Please check your credentials and try again.'
    }
  },
  pl: {
    connectionTest: {
      success: 'Test połączenia powiódł się. Kanały załadowane.',
      error: 'Test połączenia nie powiódł się. Sprawdź swoje dane uwierzytelniające i spróbuj ponownie.'
    }
  }
};

function getNotificationMessage(localeCode, type = 'success', messageKey = 'connectionTest') {
  const locale = localeCode.startsWith('pl') ? 'pl' : 'en';
  const messages = NOTIFICATION_MESSAGES[locale]?.[messageKey];

  return messages?.[type] || messages?.success || NOTIFICATION_MESSAGES.en[messageKey].success;
}

function convertTpayChannelIdInputIntoSelect(channels) {
  let tpayChannelIdFormType = document.getElementsByName('sylius_payment_method[gatewayConfig][config][tpay_channel_id]')[0];
  if (tpayChannelIdFormType === undefined) {
    return;
  }

  const value = tpayChannelIdFormType.value;

  if (tpayChannelIdFormType.tagName.toLowerCase() === 'input' && tpayChannelIdFormType.type === 'text') {
    const select = document.createElement('select');
    select.name = tpayChannelIdFormType.name;
    select.id = tpayChannelIdFormType.id;
    select.className = tpayChannelIdFormType.className;
    select.dataset.displayAllLabel = tpayChannelIdFormType.dataset.displayAllLabel;
    tpayChannelIdFormType.replaceWith(select);
    tpayChannelIdFormType = select;
  }

  tpayChannelIdFormType.innerHTML = '';

  const displayAllOption = document.createElement('option');
  displayAllOption.value = '';
  console.log(tpayChannelIdFormType.dataset);
  displayAllOption.text = tpayChannelIdFormType.dataset.displayAllLabel;

  tpayChannelIdFormType.appendChild(displayAllOption);

  for (const [id, name] of Object.entries(channels)) {
    const option = document.createElement('option');
    option.value = id;
    option.text = name;

    if (id === value) {
      option.selected = true;
    }

    tpayChannelIdFormType.appendChild(option);
  }
}
