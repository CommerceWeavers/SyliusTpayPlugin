<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface as LegacyQueryNameGeneratorInterface;
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
        LegacyQueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, OrderInterface::class, true)) {
            return;
        }

        if (!in_array($operationName, $this->allowedOrderOperationsProvider->provide(), true)) {
            $this->decorated->applyToItem($queryBuilder, $queryNameGenerator, $resourceClass, $identifiers, $operationName, $context);

            return;
        }

        $user = $this->userContext->getUser();

        if (!$user instanceof ShopUserInterface) {
            return;
        }

        $customer = $user->getCustomer();

        if ($customer === null) {
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
