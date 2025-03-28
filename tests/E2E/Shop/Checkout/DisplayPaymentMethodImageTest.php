<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Checkout;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\PaymentMethodHelperTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class DisplayPaymentMethodImageTest extends E2ETestCase
{
    use CartTrait;
    use TpayTrait;
    use LoginShopUserTrait;
    use PaymentMethodHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['addressed_cart.yaml']);
        $this->configurePaymentMethodImageByCode('tpay_card');
        $this->configurePaymentMethodWithoutUploadedFile('tpay_blik', 'https://secure.sandbox.tpay.com/tpay/web/channels/53/normal-white-bg.png');

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        // the cart is already addressed, so we go straight to selecting a shipping method
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();
    }

    public function test_it_displays_logo_for_configured_payment_method(): void
    {
        $image = $this->client->findElement(WebDriverBy::id('tpay_card_logo'));

        self::assertNotNull($image);
    }

    public function test_it_displays_default_logo_when_image_was_not_uploaded_payment_method(): void
    {
        $image = $this->client->findElement(WebDriverBy::id('tpay_blik_logo'));

        self::assertNotNull($image);
    }

    public function test_it_does_not_display_logo_if_there_is_no_default_image_and_it_was_not_uploaded(): void
    {
        self::expectException(NoSuchElementException::class);

        $this->client->findElement(WebDriverBy::id('tpay_visa_mobile_logo'));
    }
}
