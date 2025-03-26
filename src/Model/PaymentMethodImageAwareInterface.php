<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use Sylius\Component\Core\Model\ImageAwareInterface;

interface PaymentMethodImageAwareInterface extends ImageAwareInterface
{
    public function getDefaultImageUrl(): ?string;

    public function setDefaultImageUrl(?string $defaultImageUrl): void;
}
