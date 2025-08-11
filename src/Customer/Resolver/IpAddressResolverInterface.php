<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Customer\Resolver;

interface IpAddressResolverInterface
{
    public function resolve(): ?string;
}
