<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Factory;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;

interface BlikAliasFactoryInterface extends FactoryInterface
{
    public function createForCustomer(CustomerInterface $customer): BlikAliasInterface;
}
