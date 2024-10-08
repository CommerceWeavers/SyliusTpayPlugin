<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

class PayResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $transactionPaymentUrl = null,
    ) {
    }
}
