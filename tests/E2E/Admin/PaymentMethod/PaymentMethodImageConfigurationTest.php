<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Admin\PaymentMethod;

use Facebook\WebDriver\WebDriverBy;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Account\LoginAdminUserTrait;

final class PaymentMethodImageConfigurationTest extends E2ETestCase
{
    use LoginAdminUserTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures(['admin/payment_methods.yaml']);

        $this->loginAdminUser('rich@example.com', 'sylius');
    }

    public function test_it_allows_to_upload_image_for_payment_method(): void
    {
        $this->client->request('GET', '/admin/payment-methods/1/edit');

        $uploadField = $this->client->findElement(WebDriverBy::id('sylius_payment_method_image_file'));

        self::assertNotNull($uploadField);
        self::assertSame('file', $uploadField->getAttribute('type'));
    }
}
