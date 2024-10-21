<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

final class InitializeApplePaySessionResult
{
    public function __construct(
        public readonly string $result,
        public readonly string $session,
    ) {
    }
}
