<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Admin\Order\Refunds;

use Facebook\WebDriver\WebDriverBy;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginAdminUserTrait;
use Webmozart\Assert\Assert;

final class TpayRedirectVisibilityOnRefundsPageTest extends E2ETestCase
{
    use LoginAdminUserTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['admin/refund_methods.yaml']);

        $this->loginAdminUser('rich@example.com', 'sylius');
    }

    public function test_it_hides_tpay_redirect_when_no_transaction_id(): void
    {
        $this->client->request('GET', '/admin/orders/000000030/refunds');

        $paymentMethods = $this->getRefundPaymentMethods();

        self::assertStringNotContainsString('Tpay', implode(' ', $paymentMethods));
    }

    public function test_it_shows_tpay_redirect_when_transaction_id_present(): void
    {
        $this->client->request('GET', '/admin/orders/000000031/refunds');

        $paymentMethods = $this->getRefundPaymentMethods();

        self::assertStringContainsString('Tpay', implode(' ', $paymentMethods));
    }

    /** @return array<string> */
    private function getRefundPaymentMethods(): array
    {
        $select = $this->client->findElement(WebDriverBy::id('payment-methods'));
        Assert::notNull($select);

        $options = $select->findElements(WebDriverBy::cssSelector('option'));

        return array_map(fn($option): string => trim($option->getText()), $options);
    }
}
