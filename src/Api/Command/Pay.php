<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Contract\OrderTokenAwareInterface;

final class Pay implements OrderTokenAwareInterface
{
    public function __construct(
        public readonly string $orderToken,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly ?string $applePayToken = null,
        public readonly ?string $blikToken = null,
        public readonly ?string $googlePayToken = null,
        public readonly ?string $encodedCardData = null,
        public readonly ?string $tpayChannelId = null,
        public readonly bool $isVisaMobilePayment = false,
    ) {
    }

    public static function getOrderTokenPropertyName(): string
    {
        return 'orderToken';
    }
}
