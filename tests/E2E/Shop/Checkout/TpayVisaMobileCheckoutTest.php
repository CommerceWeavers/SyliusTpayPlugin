<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Checkout;

use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class TpayVisaMobileCheckoutTest extends E2ETestCase
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

    public function test_it_throws_validation_error_if_phone_number_is_too_short(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $this->processWithPaymentMethod('tpay_visa_mobile');
        $this->fillVisaMobile(self::FORM_ID, '123123');
        $this->placeOrder();

        $validationElement = $this->findElementByXpath("//div[contains(@class, 'sylius-validation-error')]");
        $this->assertNotNull($validationElement);
        $this->assertSame(
            "The mobile phone must be composed minimum of 7 digits.",
            $validationElement->getText()
        );
    }

    public function test_it_trims_input_phone_number_if_it_is_too_long(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $inputValueMaxLength = 15;

        $this->processWithPaymentMethod('tpay_visa_mobile');
        $this->fillVisaMobile(self::FORM_ID, '1234567890123456789');

        $inputValue = $this
            ->findElementByXpath("//input[@id='sylius_checkout_complete_tpay_visa_mobile_phone_number']")
            ->getAttribute('value')
        ;

        $expectedValue = '123456789012345';
        $this->assertSame('123456789012345', $inputValue);
        $this->assertSame($inputValueMaxLength, strlen($expectedValue));
    }

    public function test_it_throws_validation_error_if_phone_number_is_empty(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $this->processWithPaymentMethod('tpay_visa_mobile');
        $this->fillVisaMobile(self::FORM_ID, '');
        $this->placeOrder();

        $validationElement = $this->findElementByXpath("//div[contains(@class, 'sylius-validation-error')]");
        $this->assertNotNull($validationElement);
        $this->assertSame(
            "The mobile phone number is required.",
            $validationElement->getText()
        );
    }

    public function test_it_completes_the_checkout_using_visa_mobile(): void
    {
        $this->markTestSkipped('This test is not working yet.');
        $this->processWithPaymentMethod('tpay_visa_mobile');
        $this->fillVisaMobile(self::FORM_ID, '123123123');
        $this->placeOrder();

        $this->assertPageTitleContains('Waiting for payment');
    }
}
