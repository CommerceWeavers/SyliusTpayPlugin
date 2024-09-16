<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;

/**
 * @property Client $client
 */
trait TpayTrait
{
    public function fillCardData(string $holderName, string $cardNumber, string $cvv, string $month, string $year): void
    {
        $this->client->findElement(WebDriverBy::id('sylius_checkout_complete_tpay_card_holder_name'))->sendKeys($holderName);
        $this->client->findElement(WebDriverBy::id('sylius_checkout_complete_tpay_card_number'))->sendKeys($cardNumber);
        $this->client->findElement(WebDriverBy::id('sylius_checkout_complete_tpay_card_cvv'))->sendKeys($cvv);
        $this->client->findElement(WebDriverBy::id('sylius_checkout_complete_tpay_card_expiration_date_year'))->sendKeys($year);
    }

    public function fillBlikToken(string $blikToken): void
    {
        $this->client->findElement(WebDriverBy::id('sylius_checkout_complete_tpay_blik_token'))->sendKeys($blikToken);
    }
}
