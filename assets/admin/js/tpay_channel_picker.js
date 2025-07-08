document.addEventListener('cw_tpay:gateway_configuration:connection_tested', (event) => {
  let result = event.detail.result;

  if (result === 'failure') {
    convertTpayChannelIdInputIntoSelect([]);
  }

  const productionModeElement = document.querySelector('[data-gateway-config-production-mode]');
  const clientIdElement = document.querySelector('[data-gateway-config-client-id]');
  const clientSecretElement = document.querySelector('[data-gateway-config-client-secret]');
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

        return jsonResponse;
      });
    })
    .then(data => {
      convertTpayChannelIdInputIntoSelect(data);
    })
});

function convertTpayChannelIdInputIntoSelect(channels) {
  let channelIdElement = document.querySelector('[data-gateway-config-channel-id]');

  if (channelIdElement === undefined) {
    return;
  }

  const newChannelIdElement = document.createElement('select');
  newChannelIdElement.id = channelIdElement.id;
  newChannelIdElement.name = channelIdElement.name;
  newChannelIdElement.classList.add('form-select');

  Object.assign(newChannelIdElement.dataset, channelIdElement.dataset);

  newChannelIdElement.querySelectorAll('option').forEach(option => option.remove());

  const defaultOption = document.createElement('option');
  defaultOption.value = '';
  defaultOption.innerText = channelIdElement.dataset.defaultOptionLabel;

  newChannelIdElement.appendChild(defaultOption);

  for (const [key,value] of Object.entries(channels)) {
    const option = document.createElement('option');
    option.value = key;
    option.innerText = value;

    newChannelIdElement.appendChild(option);
  }

  channelIdElement.replaceWith(newChannelIdElement);
}
