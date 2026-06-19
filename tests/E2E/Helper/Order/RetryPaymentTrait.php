<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use Symfony\Component\Panther\Client;

/**
 * @property Client $client
 */
trait RetryPaymentTrait
{
    public function showPaymentFailedPage(string $orderToken): void
    {
        $this->client->get(sprintf('/en_US/tpay/order/%s/payment-failed', $orderToken));
    }

    public function retryPayment(): void
    {
        $this->client->clickLink('Retry payment');
    }
}
