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
        $this->client->findElement(WebDriverBy::xpath("//div[@data-bank-id='1']"))->click();
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
        $element = $this->client->findElement(WebDriverBy::xpath('//*[@id="sylius-payment-methods"]/form/div[1]/div/div[2]'));

        $this->assertStringContainsString('One Bank (Tpay)', $element->getText());
        $this->assertStringContainsString('One Bank With Amount Min 20 Constraint (Tpay)', $element->getText());
        $this->assertStringNotContainsString('One Bank With Amount Min 30 Constraint (Tpay)', $element->getText());
    }
}
