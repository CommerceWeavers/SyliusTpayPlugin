<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\Panther\Client;

/**
 * @property Client $client
 */
trait TpayTrait
{
    public function fillCardData(string $formId, string $cardNumber, string $cvv, string $month, string $year, bool $saveCardForLater = false): void
    {
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_card_number', $formId)))->sendKeys($cardNumber);
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_card_cvv', $formId)))->sendKeys($cvv);
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_card_expiration_date_month', $formId)))->sendKeys($month);
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_card_expiration_date_year', $formId)))->sendKeys($year);

        if ($saveCardForLater) {
            $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_save_credit_card_for_later', $formId)))->sendKeys(true);
        }
    }

    public function fillBlikToken(string $formId, string $blikToken): void
    {
        $this->client->waitForElementToContain('h1', 'Summary of your order');
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_blik_token', $formId)))->sendKeys($blikToken);
    }

    public function fillVisaMobile(string $formId, string $mobilePhone): void
    {
        $this->client->waitForVisibility(sprintf('#%s_tpay_visa_mobile_phone_number', $formId));
        $this->client->findElement(WebDriverBy::id(sprintf('%s_tpay_visa_mobile_phone_number', $formId)))->sendKeys($mobilePhone);
    }

    public function findElementByXpath(string $xpath): WebDriverElement
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath));
    }
}
