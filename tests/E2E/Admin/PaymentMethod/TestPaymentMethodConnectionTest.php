<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Admin\PaymentMethod;

use Facebook\WebDriver\WebDriverBy;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginAdminUserTrait;

final class TestPaymentMethodConnectionTest extends E2ETestCase
{
    use LoginAdminUserTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['admin/payment_methods.yaml']);

        $this->loginAdminUser('rich@example.com', 'sylius');
    }

    public function test_it_checks_if_payment_method_connection(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $this->client->request('GET', '/admin/payment-methods/new/tpay_pbl_channel');

        $this->client
            ->findElement(WebDriverBy::id('sylius_payment_method_gatewayConfig_config_client_id'))
            ->sendKeys(getenv('TPAY_CLIENT_ID'))
        ;
        $this->client
            ->findElement(WebDriverBy::id('sylius_payment_method_gatewayConfig_config_client_secret'))
            ->sendKeys(getenv('TPAY_CLIENT_SECRET'))
        ;
        $this->client
            ->findElement(WebDriverBy::id('sylius_payment_method_gatewayConfig_config_production_mode'))
            ->sendKeys('No')
        ;
        $this->client->findElement(WebDriverBy::id('test-connection-button'))->click();
        $this->client->waitForElementToContain('#test-connection-message', 'Connection test successful. Channels loaded.');
        $channelSelector = $this->client->findElement(WebDriverBy::id('sylius_payment_method_gatewayConfig_config_tpay_channel_id'));

        $this->assertSame('select', $channelSelector->getTagName());
    }
}
