<?php

declare(strict_types=1);


namespace CommerceWeavers\SyliusTpayPlugin\Repository;

use CommerceWeavers\SyliusTpayPlugin\Model\BlikAliasInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

interface BlikAliasRepositoryInterface extends RepositoryInterface
{
    public function findOneByCustomer(CustomerInterface $customer): ?BlikAliasInterface;
}
