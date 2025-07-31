<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;

class NotifyData
{
    public function __construct(
        public readonly string $jws,
        public readonly string $requestContent,
        public readonly array $requestParameters,
    ) {
    }
}
