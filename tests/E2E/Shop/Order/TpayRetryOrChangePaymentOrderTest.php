<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Shop\Order;

use Facebook\WebDriver\WebDriverBy;
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

    public function test_it_retries_payment_using_blik(): void
    {
        $this->loadFixtures(['blik_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $this->fillBlikToken(self::SELECT_FIRST_PAYMENT_FORM_ID, '777123');
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Waiting for payment');
    }

    /**
     * @group dev
     */
    public function test_it_does_not_allow_to_complete_retrying_blik_payment_without_filling_blik_token(): void
    {
        $this->loadFixtures(['blik_unpaid_order.yaml']);
        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $this->fillBlikToken(self::SELECT_FIRST_PAYMENT_FORM_ID, '');
        $this->client->submitForm('Pay');

        $input = $this->client->findElement(WebDriverBy::id(\sprintf('%s_tpay_blik_token', self::SELECT_FIRST_PAYMENT_FORM_ID)));
        self::assertStringContainsString('is-invalid', $input->getAttribute('class') ?? '');

        $inputFieldWrapper = $this->client->findElement(WebDriverBy::cssSelector('div.field.mb-3.required.error'));
        $validationErrorMessageElement = $inputFieldWrapper->findElement(WebDriverBy::cssSelector('div.invalid-feedback'));
        self::assertNotNull($validationErrorMessageElement);
        self::assertSame(
            'This value should not be blank.',
            $validationErrorMessageElement->getText(),
        );
    }

    /** @group requires-fixes */
    public function test_it_retries_payment_using_card(): void
    {
        $this->loadFixtures(['card_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $this->fillCardData(self::SELECT_FIRST_PAYMENT_FORM_ID, self::CARD_NUMBER, self::CARD_CVC, self::CARD_EXPIRATION_DATE_MONTH, self::CARD_EXPIRATION_DATE_YEAR);

        $this->client->findElement(WebDriverBy::cssSelector('form'))->submit();

        $this->client->waitForElementToContain('title', 'Waiting for payment');
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

    /** @group requires-fixes */
    public function test_it_changes_payment_to_card(): void
    {
        $this->loadFixtures(['blik_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'Card (Tpay)')]"))->click();
        $this->fillCardData(self::SELECT_FIRST_PAYMENT_FORM_ID, self::CARD_NUMBER, self::CARD_CVC, self::CARD_EXPIRATION_DATE_MONTH, self::CARD_EXPIRATION_DATE_YEAR);
        $this->client->submitForm('Pay');

        $this->assertPageTitleContains('Waiting for payment | Web Channel');
    }

    public function test_it_changes_payment_to_pay_by_link(): void
    {
        $this->loadFixtures(['pbl_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'Choose bank (Tpay)')]"))->click();
        $form->getElement()->findElement(WebDriverBy::xpath("//div[@data-live-channel-id-param='1']"))->click();
        $this->client->waitForAttributeToContain(
            '[data-live-channel-id-param="1"]',
            'class',
            'selected',
        );
        $this->client->submitForm('Pay');

        self::assertPageTitleContains('Thank you');
    }

    public function test_it_changes_payment_to_visa_mobile(): void
    {
        $this->loadFixtures(['visa_mobile_unpaid_order.yaml']);

        $this->loginShopUser('tony@nonexisting.cw', 'sylius');

        $this->client->get('/en_US/order/tokenValue1');
        $form = $this->client->getCrawler()->selectButton('Pay')->form();
        $form->getElement()->findElement(WebDriverBy::xpath("//label[contains(text(),'Visa mobile (Tpay)')]"))->click();
        $this->fillVisaMobile(self::SELECT_FIRST_PAYMENT_FORM_ID, '123123123');
        $this->client->submitForm('Pay');

        self::assertPageTitleContains('Waiting for payment');
    }
}
