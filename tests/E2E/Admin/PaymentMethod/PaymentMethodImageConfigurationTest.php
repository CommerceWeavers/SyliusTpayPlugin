<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Admin\PaymentMethod;

use Facebook\WebDriver\Remote\LocalFileDetector;
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
        $defaultImageUrlField = $this->client->findElement(WebDriverBy::id('sylius_admin_payment_method_defaultImageUrl'));

        self::assertNotNull($defaultImageUrlField);
        self::assertSame('true', $defaultImageUrlField->getAttribute('readonly'));
    }

    public function test_it_allows_to_upload_image_for_payment_method(): void
    {
        $this->uploadPaymentMethodImage();

        self::assertSelectorExists('img[alt="method-logo"]');
        self::assertSelectorAttributeContains('button.btn-danger', 'formaction', 'remove-image');
    }

    public function test_it_allows_to_remove_uploaded_image(): void
    {
        $this->uploadPaymentMethodImage();

        $this->client->findElement(WebDriverBy::cssSelector('button.btn-danger'))->click();

        self::assertSelectorNotExists('img[alt="method-logo"]');
        self::assertSelectorNotExists('button.btn-danger');
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

    private function uploadPaymentMethodImage(): void
    {
        $uploadField = $this->client->findElement(WebDriverBy::id('sylius_admin_payment_method_image_file'));
        $uploadField->setFileDetector(new LocalFileDetector());
        $uploadField->sendKeys($this->getImagePath());

        $this->client->submitForm('Update');
    }

    private function getImagePath(): string|false
    {
        return realpath(__DIR__ . '/../../Resources/media/test-image.gif');
    }
}
