<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

final class PayByBlik
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $blikToken,
        public readonly bool $blikSaveAlias,
    ) {
    }
}
