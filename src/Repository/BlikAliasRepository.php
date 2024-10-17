<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Repository;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\CustomerInterface;

final class BlikAliasRepository extends EntityRepository implements BlikAliasRepositoryInterface
{
    public function findOneByCustomer(CustomerInterface $customer): ?BlikAliasInterface
    {
        /** @phpstan-var BlikAliasInterface|null $blikAlias */
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
