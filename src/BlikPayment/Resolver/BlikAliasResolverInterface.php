<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\BlikPayment\Resolver;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface BlikAliasResolverInterface
{
    public function resolve(CustomerInterface $customer): BlikAliasInterface;
}
