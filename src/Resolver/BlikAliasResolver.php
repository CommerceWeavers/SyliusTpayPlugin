<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Factory\BlikAliasFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use Sylius\Component\Core\Model\CustomerInterface;

final class BlikAliasResolver implements BlikAliasResolverInterface
{
    public function __construct(
        private readonly BlikAliasRepositoryInterface $blikAliasRepository,
        private readonly BlikAliasFactoryInterface $blikAliasFactory,
    ) {
    }

    public function resolve(CustomerInterface $customer): BlikAliasInterface
    {
        return $this->blikAliasRepository->findOneByCustomer($customer)
            ?? $this->blikAliasFactory->createForCustomer($customer);
    }
}
