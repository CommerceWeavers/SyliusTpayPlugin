<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

final class PayByVisaMobile
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $visaMobilePhoneNumber,
    ) {
    }
}
