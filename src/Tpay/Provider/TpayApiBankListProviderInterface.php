<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

interface TpayApiBankListProviderInterface
{
    public function provide(): array;
}
