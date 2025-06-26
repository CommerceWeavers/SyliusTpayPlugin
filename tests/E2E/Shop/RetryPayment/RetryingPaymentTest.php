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
        $this->markTestSkipped('This test is not working yet.');
        $this->doPlaceOrder('t0k3n', productVariantCode: 'MUG');

        $this->showPaymentFailedPage('t0k3n');
        $this->retryPayment();

        $this->assertPageTitleContains('Summary of your order');
        $this->assertSelectorWillContain('.sylius-flash-message', 'The previous payment has been cancelled');
    }

    public function test_it_prevents_retrying_not_qualifying_payments(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $order = $this->doPlaceOrder('t0k3n', productVariantCode: 'MUG');
        $this->payOrder($order);

        $this->showPaymentFailedPage('t0k3n');
        $this->retryPayment();

        $this->assertSelectorWillContain('.sylius-flash-message', 'This payment cannot be retried');
    }
}
