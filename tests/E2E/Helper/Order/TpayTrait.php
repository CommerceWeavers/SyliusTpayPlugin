<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Symfony\Component\Panther\Client;

/**
 * @property Client $client
 */
trait TpayTrait
{
    public function fillCardData(string $formId, string $cardNumber, string $cvv, string $month, string $year, bool $saveCardForLater = false): void
    {
        $this->client->waitForVisibility('[data-tpay-card-number]');

        $this->client->findElement(WebDriverBy::cssSelector('[data-tpay-card-number]'))->sendKeys($cardNumber);
        $this->client->findElement(WebDriverBy::cssSelector('[data-tpay-cvc]'))->sendKeys($cvv);

        $selectElement = $this->client->findElement(WebDriverBy::cssSelector('[data-tpay-expiration-month]'));

        $select = new WebDriverSelect($selectElement);
        $select->selectByValue($month);

        $this->client->findElement(WebDriverBy::cssSelector('[data-tpay-expiration-year]'))->sendKeys($year);

        if ($saveCardForLater) {
            $this->client->findElement(WebDriverBy::cssSelector('[data-tpay-save-card] > input'))->sendKeys(true);
        }
    }

    public function fillBlikToken(string $formId, string $blikToken): void
    {
        $this->client->waitForVisibility(sprintf('#%s_tpay_blik_token', $formId));
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
