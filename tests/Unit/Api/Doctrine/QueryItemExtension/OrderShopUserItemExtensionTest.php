<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Doctrine\QueryItemExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\OrderShopUserItemExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider\AllowedOrderOperationsProviderInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;

final class OrderShopUserItemExtensionTest extends TestCase
{
    use ProphecyTrait;

    private QueryItemExtensionInterface|QueryCollectionExtensionInterface|ObjectProphecy $decorated;

    private UserContextInterface|ObjectProphecy $userContext;

    private AllowedOrderOperationsProviderInterface|ObjectProphecy $allowedOrderOperationsProvider;

    private QueryBuilder|ObjectProphecy $queryBuilder;

    private QueryNameGeneratorInterface|ObjectProphecy $queryNameGenerator;

    private OrderShopUserItemExtension $extension;

    protected function setUp(): void
    {
        $this->decorated = $this->prophesize(QueryItemExtensionInterface::class);
        $this->decorated->willImplement(QueryCollectionExtensionInterface::class);
        $this->userContext = $this->prophesize(UserContextInterface::class);
        $this->allowedOrderOperationsProvider = $this->prophesize(AllowedOrderOperationsProviderInterface::class);
        $this->queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->queryNameGenerator = $this->prophesize(QueryNameGeneratorInterface::class);

        $this->extension = new OrderShopUserItemExtension(
            $this->decorated->reveal(),
            $this->userContext->reveal(),
            $this->allowedOrderOperationsProvider->reveal(),
        );
    }

    public function test_apply_to_item_does_nothing_when_resource_is_not_order(): void
    {
        $this->decorated->applyToItem(Argument::cetera())->shouldNotBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            \stdClass::class,
            ['id' => 1],
            null,
            [],
        );
    }

    public function test_apply_to_item_delegates_to_decorated_when_operation_is_null(): void
    {
        $this->decorated
            ->applyToItem(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                ['id' => 1],
                null,
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            null,
            [],
        );
    }

    public function test_apply_to_item_delegates_to_decorated_when_operation_is_not_allowed(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('not_allowed_operation');

        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $this->decorated
            ->applyToItem(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                ['id' => 1],
                $operation->reveal(),
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_item_does_nothing_when_user_is_not_shop_user(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $adminUser = $this->prophesize(AdminUserInterface::class);
        $this->userContext->getUser()->willReturn($adminUser->reveal());

        $this->queryBuilder->getRootAliases()->shouldNotBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_item_does_nothing_when_user_is_null(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $this->userContext->getUser()->willReturn(null);

        $this->queryBuilder->getRootAliases()->shouldNotBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_item_delegates_to_decorated_when_customer_is_null(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $shopUser = $this->prophesize(ShopUserInterface::class);
        $shopUser->getCustomer()->willReturn(null);
        $this->userContext->getUser()->willReturn($shopUser->reveal());

        $this->decorated
            ->applyToItem(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                ['id' => 1],
                $operation->reveal(),
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_item_adds_customer_filter_when_all_conditions_are_met(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getId()->willReturn(123);

        $shopUser = $this->prophesize(ShopUserInterface::class);
        $shopUser->getCustomer()->willReturn($customer->reveal());
        $this->userContext->getUser()->willReturn($shopUser->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->queryNameGenerator->generateParameterName('customer')->willReturn('customer_p1');

        $this->queryBuilder->andWhere('o.customer = :customer_p1')->willReturn($this->queryBuilder->reveal())->shouldBeCalled();
        $this->queryBuilder->setParameter('customer_p1', 123)->willReturn($this->queryBuilder->reveal())->shouldBeCalled();

        $this->extension->applyToItem(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            ['id' => 1],
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_collection_does_nothing_when_resource_is_not_order(): void
    {
        $this->decorated->applyToCollection(Argument::cetera())->shouldNotBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            \stdClass::class,
            null,
            [],
        );
    }

    public function test_apply_to_collection_delegates_to_decorated_when_operation_is_null(): void
    {
        $this->decorated
            ->applyToCollection(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                null,
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            null,
            [],
        );
    }

    public function test_apply_to_collection_delegates_to_decorated_when_operation_is_not_allowed(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('not_allowed_operation');

        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $this->decorated
            ->applyToCollection(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                $operation->reveal(),
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_collection_does_nothing_when_user_is_not_shop_user(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $adminUser = $this->prophesize(AdminUserInterface::class);
        $this->userContext->getUser()->willReturn($adminUser->reveal());

        $this->queryBuilder->getRootAliases()->shouldNotBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_collection_does_nothing_when_user_is_null(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $this->userContext->getUser()->willReturn(null);

        $this->queryBuilder->getRootAliases()->shouldNotBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_collection_delegates_to_decorated_when_customer_is_null(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $shopUser = $this->prophesize(ShopUserInterface::class);
        $shopUser->getCustomer()->willReturn(null);
        $this->userContext->getUser()->willReturn($shopUser->reveal());

        $this->decorated
            ->applyToCollection(
                $this->queryBuilder->reveal(),
                $this->queryNameGenerator->reveal(),
                OrderInterface::class,
                $operation->reveal(),
                [],
            )
            ->shouldBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            $operation->reveal(),
            [],
        );
    }

    public function test_apply_to_collection_adds_customer_filter_when_all_conditions_are_met(): void
    {
        $operation = $this->prophesize(Operation::class);
        $operation->getName()->willReturn('allowed_operation');
        $this->allowedOrderOperationsProvider->provide()->willReturn(['allowed_operation']);

        $customer = $this->prophesize(CustomerInterface::class);
        $customer->getId()->willReturn(123);

        $shopUser = $this->prophesize(ShopUserInterface::class);
        $shopUser->getCustomer()->willReturn($customer->reveal());
        $this->userContext->getUser()->willReturn($shopUser->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->queryNameGenerator->generateParameterName('customer')->willReturn('customer_p1');

        $this->queryBuilder->andWhere('o.customer = :customer_p1')->willReturn($this->queryBuilder->reveal())->shouldBeCalled();
        $this->queryBuilder->setParameter('customer_p1', 123)->willReturn($this->queryBuilder->reveal())->shouldBeCalled();

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator->reveal(),
            OrderInterface::class,
            $operation->reveal(),
            [],
        );
    }
}
