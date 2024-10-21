<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Contract\PaymentIdAwareInterface;

final class PaymentIdAwareContextBuilder extends AbstractAwareContextBuilder
{
    public function getAttributeKey(): string
    {
        return 'paymentId';
    }

    public function getSupportedInterface(): string
    {
        return PaymentIdAwareInterface::class;
    }

    public function getPropertyNameAccessorMethodName(): string
    {
        return 'getPaymentIdPropertyName';
    }
}
