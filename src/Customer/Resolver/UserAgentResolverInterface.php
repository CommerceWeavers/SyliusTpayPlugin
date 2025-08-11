<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Customer\Resolver;

interface UserAgentResolverInterface
{
    public function resolve(): ?string;
}
