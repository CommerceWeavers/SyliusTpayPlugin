<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

interface TpayApiChannelListProviderInterface
{
    public function provide(): array;
}
