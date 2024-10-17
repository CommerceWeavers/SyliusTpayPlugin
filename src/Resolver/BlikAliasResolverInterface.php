<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Model\BlikAliasInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface BlikAliasResolverInterface
{
    public function resolve(CustomerInterface $customer): BlikAliasInterface;
}
