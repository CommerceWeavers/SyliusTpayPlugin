<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;

readonly class NotifyData
{
    public function __construct(
        public string $jws,
        public string $requestContent,
        public array $requestParameters,
    ) {
    }
}
