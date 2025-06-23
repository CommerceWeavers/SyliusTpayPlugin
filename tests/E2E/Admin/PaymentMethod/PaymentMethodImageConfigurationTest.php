<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Admin\PaymentMethod;

use Facebook\WebDriver\WebDriverBy;
use TestApp\Entity\PaymentMethod;
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
        $this->goToEditPaymentMethodPage();
    }

    public function test_it_has_readonly_default_image_url_field(): void
    {
        $defaultImageUrlField = $this->client->findElement(WebDriverBy::id('sylius_payment_method_defaultImageUrl'));

        self::assertNotNull($defaultImageUrlField);
        self::assertSame('true', $defaultImageUrlField->getAttribute('readonly'));
    }

    public function test_it_allows_to_upload_image_for_payment_method(): void
    {
        $uploadField = $this->client->findElement(WebDriverBy::id('sylius_payment_method_image_file'));

        self::assertNotNull($uploadField);
        self::assertSame('file', $uploadField->getAttribute('type'));
    }

    private function goToEditPaymentMethodPage(): void
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = self::getContainer()
            ->get('sylius.repository.payment_method')
            ->findOneBy(['code' => 'tpay_card'])
        ;

        $this->client->request(
            'GET',
            \sprintf('/admin/payment-methods/%s/edit', $paymentMethod->getId()),
        );
    }
}
