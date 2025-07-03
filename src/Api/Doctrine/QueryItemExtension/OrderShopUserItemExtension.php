<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider\AllowedOrderOperationsProviderInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class OrderShopUserItemExtension implements QueryItemExtensionInterface
{
    public function __construct(
        private readonly QueryItemExtensionInterface $decorated,
        private readonly UserContextInterface $userContext,
        private readonly AllowedOrderOperationsProviderInterface $allowedOrderOperationsProvider,
    ) {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, OrderInterface::class, true)) {
            return;
        }

        if ($operation === null || !in_array($operation->getName(), $this->allowedOrderOperationsProvider->provide(), true)) {
            $this->decorated->applyToItem($queryBuilder, $queryNameGenerator, $resourceClass, $identifiers, $operation, $context);

            return;
        }

        $user = $this->userContext->getUser();

        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $customer = $user->getCustomer();

        if ($customer === null) {
            $this->decorated->applyToItem($queryBuilder, $queryNameGenerator, $resourceClass, $identifiers, $operation, $context);

            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $customerParameterName = $queryNameGenerator->generateParameterName('customer');

        $queryBuilder
            ->andWhere(sprintf('%s.customer = :%s', $rootAlias, $customerParameterName))
            ->setParameter($customerParameterName, $customer->getId())
        ;
    }
}
