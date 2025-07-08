<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\RetryPayment;

use Tests\CommerceWeavers\SyliusTpayPlugin\Api\Utils\OrderPlacerTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\RetryPaymentTrait;

final class RetryingPaymentTest extends E2ETestCase
{
    use OrderPlacerTrait;
    use RetryPaymentTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpOrderPlacer();

        $this->loadFixtures(['addressed_cart.yaml']);
    }

    public function test_it_retries_payment(): void
    {
        $this->doPlaceOrder('t0k3n', productVariantCode: 'MUG');

        $this->showPaymentFailedPage('t0k3n');
        $this->retryPayment();

        $this->assertPageTitleContains('Summary of your order');
        $this->assertSelectorWillContain('.alert', 'The previous payment has been cancelled');
    }

    public function test_it_prevents_retrying_not_qualifying_payments(): void
    {
        $order = $this->doPlaceOrder('t0k3n', productVariantCode: 'MUG');
        $this->payOrder($order);

        $this->showPaymentFailedPage('t0k3n');
        $this->retryPayment();

        $this->assertSelectorWillContain('.alert', 'This payment cannot be retried');
    }
}
