<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use Symfony\Component\Panther\Client;

/**
 * @property Client $client
 */
trait CartTrait
{
    public function showSelectingShippingMethodStep(): void
    {
        $this->client->get('/en_US/checkout/select-shipping');
    }

    public function processWithDefaultShippingMethod(): void
    {
        $this->client->submitForm('Next');
    }

    public function processWithPaymentMethod(string $paymentMethodCode): void
    {
        $this->client->executeScript(
            sprintf(
                'document.querySelector(\'[name="sylius_checkout_select_payment[payments][0][method]"][value="%s"]\').checked = true',
                $paymentMethodCode,
            ),
        );
        $this->client->submitForm('Next');
    }

    public function placeOrder(): void
    {
        $this->client->submitForm('Place order');
    }
}
