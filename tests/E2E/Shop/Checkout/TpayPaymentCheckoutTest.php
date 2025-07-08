<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Checkout;

use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class TpayPaymentCheckoutTest extends E2ETestCase
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
        // the cart is already addressed, so we go straight to selecting a shipping method
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();
    }

    public function test_it_completes_the_checkout(): void
    {
        $this->processWithPaymentMethod('tpay');
        $this->placeOrder();

        $this->assertPageTitleContains('Thank you!');
    }

    public function test_it_completes_the_checkout_using_blik(): void
    {
        $this->processWithPaymentMethod('tpay_blik');
        $this->fillBlikToken(self::FORM_ID, '777123');
        $this->placeOrder();

        $this->assertPageTitleContains('Waiting for payment');
    }

    public function test_it_fails_completing_the_checkout_using_invalid_blik_token(): void
    {
        $this->processWithPaymentMethod('tpay_blik');
        $this->fillBlikToken(self::FORM_ID, '999123');
        $this->placeOrder();

        $this->assertPageTitleContains('Something went wrong with your payment | Web Channel');
        $this->assertSame(
            'Podany kod jest nieprawidłowy, bądź utracił ważność.',
            $this->findElementByXpath("//div[contains(@class, 'alert') and contains(@class, 'alert-danger')]")->getText(),
        );
    }
}
