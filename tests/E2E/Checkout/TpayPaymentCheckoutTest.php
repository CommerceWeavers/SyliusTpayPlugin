<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Checkout;

use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;

final class TpayPaymentCheckoutTest extends E2ETestCase
{
    use CartTrait;
    use LoginShopUserTrait;

    public function test_it_completes_the_checkout(): void
    {
        $this->loadFixtures([
            'addressed_cart.yaml',
            'channel.yaml',
            'country.yaml',
            'customer.yaml',
            'payment_method.yaml',
            'shipping_category.yaml',
            'shipping_method.yaml',
            'tax_category.yaml',
        ]);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        // the cart is already addressed, so we go straight to selecting a shipping method
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();
        $this->processWithPaymentMethod('tpay');
        $this->placeOrder();

        $this->assertPageTitleContains('Thank you!');
    }
}
