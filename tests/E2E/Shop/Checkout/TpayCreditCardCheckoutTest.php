<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Checkout;

use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\CartTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\PaymentNotificationTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayNotificationPayloadTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class TpayCreditCardCheckoutTest extends E2ETestCase
{
    use CartTrait;
    use TpayTrait;
    use LoginShopUserTrait;
    use PaymentNotificationTrait;
    use TpayNotificationPayloadTrait;

    private const FORM_ID = 'sylius_checkout_complete';

    private const CARD_NUMBER = '4012 0010 3714 1112';

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['addressed_cart.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');
        $this->showSelectingShippingMethodStep();
        $this->processWithDefaultShippingMethod();
        $this->resetEntityManager();
    }

    /**
     * @group checkout
     */
    public function test_it_completes_the_checkout_using_credit_card(): void
    {
        $this->processWithPaymentMethod('tpay_card');
        $this->fillCardData(self::FORM_ID, self::CARD_NUMBER, '123', '01', '2029');
        $this->placeOrder();

        self::assertPageTitleContains('Waiting for payment');

        $this->triggerNotificationForLastPayment($this->getCreditCardNotificationPayload());

        $this->client->waitForElementToContain('body', 'Thank you!');

        self::assertPageTitleContains('Thank you!');
    }

    /**
     * @group checkout
     */
    public function test_it_completes_the_checkout_using_credit_card_and_saves_the_card(): void
    {
        $this->processWithPaymentMethod('tpay_card');
        $this->fillCardData(self::FORM_ID, self::CARD_NUMBER, '123', '01', '2029', true);
        $this->placeOrder();

        $this->triggerNotificationForLastPayment($this->getCreditCardNotificationPayload());
        $this->client->get('/en_US/account/credit-cards');

        self::assertSelectorTextContains('[data-test-grid-table-body]', 'VISA 1111 01-2029');
    }
}
