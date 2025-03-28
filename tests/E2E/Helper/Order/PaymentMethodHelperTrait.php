<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E\Helper\Order;

use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use CommerceWeavers\SyliusTpayPlugin\Entity\PaymentMethodImage;
use Tests\CommerceWeavers\SyliusTpayPlugin\E2E\E2ETestCase;

/** @mixin E2ETestCase */
trait PaymentMethodHelperTrait
{
    private function configurePaymentMethodImageByCode(string $paymentMethodCode): void
    {
        /** @var PaymentMethodRepository $repository */
        $repository = self::getContainer()->get('sylius.repository.payment_method');

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $repository->findOneBy(['code' => $paymentMethodCode]);

        $image = new PaymentMethodImage();
        $image->setPath('path/to/file.png');

        self::getContainer()->get('doctrine.orm.default_entity_manager')->persist($image);

        $paymentMethod->setImage($image);

        self::getContainer()->get('doctrine.orm.default_entity_manager')->flush();
    }

    private function configurePaymentMethodWithoutUploadedFile(string $paymentMethodCode, ?string $imageUrl = null): void
    {
        /** @var PaymentMethodRepository $repository */
        $repository = self::getContainer()->get('sylius.repository.payment_method');

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $repository->findOneBy(['code' => $paymentMethodCode]);

        $paymentMethod->setDefaultImageUrl($imageUrl);

        self::getContainer()->get('doctrine.orm.default_entity_manager')->flush();
    }
}
