<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Checkout;

use Facebook\WebDriver\WebDriverBy;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class TpayPayByLinkCheckoutTest extends E2ETestCase
{
    use CartTrait;
    use TpayTrait;
    use LoginShopUserTrait;

    private const FORM_ID = 'sylius_checkout_complete';

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['addressed_cart.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();
    }

    public function test_it_completes_the_checkout_using_pay_by_link_channel_selection(): void
    {
        $this->processWithPaymentMethod('tpay_pbl');

        $this->client->waitForElementToContain('h1', 'Summary of your order');
        $this->client->findElement(WebDriverBy::xpath("//div[@data-live-channel-id-param='1']"))->click();
        $this->client->waitForAttributeToContain(
            '[data-live-channel-id-param="1"]',
            'class',
            'selected',
        );

        $this->placeOrder();

        $this->assertPageTitleContains('Thank you!');
    }

    public function test_it_completes_the_checkout_using_pay_by_link_channel_preselected(): void
    {
        $this->processWithPaymentMethod('tpay_pbl_one_channel');
        $this->placeOrder();

        $this->assertPageTitleContains('Thank you!');
    }

    public function test_it_cannot_complete_the_checkout_using_not_supported_pay_by_link(): void
    {
        $picker = $this->client->findElement(WebDriverBy::xpath('//*[@data-live-name-value="sylius_shop:checkout:payment:form"]'));

        $this->assertStringContainsString('One Bank (Tpay)', $picker->getText());
        $this->assertStringContainsString('One Bank With Amount Min 20 Constraint (Tpay)', $picker->getText());
        $this->assertStringNotContainsString('One Bank With Amount Min 30 Constraint (Tpay)', $picker->getText());
    }

    public function test_it_cannot_complete_the_checkout_if_no_channel_is_selected(): void
    {
        $this->processWithPaymentMethod('tpay_pbl');

        $this->client->waitForElementToContain('h1', 'Summary of your order');

        $this->placeOrder();

        $this->assertPageTitleContains('Complete |');

        foreach ($this->client->findElements(WebDriverBy::cssSelector('[data-test-validation-error]')) as $validationError) {
            if ($validationError->getText() === 'Please select a bank') {
                return;
            }
        }

        $this->fail('No validation error for bank selection');
    }
}
