<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command\Contract;

interface PaymentIdAwareInterface
{
    public static function getPaymentIdPropertyName(): string;
}
