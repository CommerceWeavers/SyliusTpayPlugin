<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Order;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginShopUserTrait;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order\TpayTrait;

final class TpayRetryOrChangePaymentOrderTest extends E2ETestCase
{
    use TpayTrait;
    use LoginShopUserTrait;

    private const SELECT_FIRST_PAYMENT_FORM_ID = 'sylius_checkout_select_payment_payments_0';

    private const CARD_NUMBER = '4012 0010 3714 1112';

    private const CARD_CVC = '123';

    private const CARD_EXPIRATION_DATE_MONTH = '01';

    private const CARD_EXPIRATION_DATE_YEAR = '2029';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_retries_payment_using_blik(): void
    {
        $this->loadFixtures(['blik_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $this->fillBlikToken(self::SELECT_FIRST_PAYMENT_FORM_ID, '777123');
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Waiting for payment');
    }

    public function test_it_retries_payment_using_card(): void
    {
        $this->loadFixtures(['card_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $this->fillCardData(self::SELECT_FIRST_PAYMENT_FORM_ID, 'John Doe', self::CARD_NUMBER, self::CARD_CVC, self::CARD_EXPIRATION_DATE_MONTH, self::CARD_EXPIRATION_DATE_YEAR);
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Thank you');
    }

    public function test_it_changes_payment_to_blik(): void
    {
        $this->loadFixtures(['card_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'BLIK (Tpay)')]"))->click();
        $this->fillBlikToken(self::SELECT_FIRST_PAYMENT_FORM_ID, '777123');
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Waiting for payment');
    }

    public function test_it_changes_payment_to_card(): void
    {
        $this->loadFixtures(['blik_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'Card (Tpay)')]"))->click();
        $this->fillCardData(self::SELECT_FIRST_PAYMENT_FORM_ID, 'John Doe', self::CARD_NUMBER, self::CARD_CVC, self::CARD_EXPIRATION_DATE_MONTH, self::CARD_EXPIRATION_DATE_YEAR);
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Thank you');
    }

    public function test_it_changes_payment_to_pay_by_link(): void
    {
        $this->loadFixtures(['pbl_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'Bank (Tpay)')]"))->click();
        $form->getElement()->findElement(WebDriverBy::cssSelector('.bank-tile[data-bank-id="1"]'))->click();
        $this->client->submitForm('Pay');

        self::assertPageTitleContains('Thank you');
    }
}
