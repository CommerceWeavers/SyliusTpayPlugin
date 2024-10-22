<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\ShowTpayPaymentMethod;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

class ShowTpayPaymentMethodOnCheckoutTest extends E2ETestCase
{
    use CartTrait;
    use TpayTrait;
    use LoginShopUserTrait;

    public function test_it_has_no_tpay_payment_method_if_currency_is_not_pln(): void
    {
        $this->loadFixtures(['showTpayPaymentMethod/usd_addressed_cart.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        // the cart is already addressed, so we go straight to selecting a shipping method
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();

        $this->expectException(NoSuchElementException::class);
        $this->getPaymentMethodByName('Tpay');
    }

    public function test_it_has_tpay_payment_method_if_currency_is_pln(): void
    {
        $this->loadFixtures(['addressed_cart.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        // the cart is already addressed, so we go straight to selecting a shipping method
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();

        $element = $this->getPaymentMethodByName('Tpay');
        $this->assertNotNull($element);
    }
}
