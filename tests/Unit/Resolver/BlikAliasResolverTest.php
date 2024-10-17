<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Factory\BlikAliasFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\CustomerInterface;

final class BlikAliasResolverTest extends TestCase
{
    use ProphecyTrait;

    private BlikAliasRepositoryInterface|ObjectProphecy $blikAliasRepository;

    private BlikAliasFactoryInterface|ObjectProphecy $blikAliasFactory;

    private CustomerInterface|ObjectProphecy $customer;

    protected function setUp(): void
    {
        $this->blikAliasRepository = $this->prophesize(BlikAliasRepositoryInterface::class);
        $this->blikAliasFactory = $this->prophesize(BlikAliasFactoryInterface::class);
        $this->customer = $this->prophesize(CustomerInterface::class);
    }

    public function test_it_resolves_blik_alias_from_repository(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class)->reveal();
        $this->blikAliasRepository->findOneByCustomer($this->customer)->willReturn($blikAlias);

        $result = (new BlikAliasResolver(
            $this->blikAliasRepository->reveal(),
            $this->blikAliasFactory->reveal(),
        ))->resolve($this->customer->reveal());

        $this->assertSame($blikAlias, $result);
    }

    public function test_it_resolves_blik_alias_from_factory(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class)->reveal();
        $this->blikAliasRepository->findOneByCustomer($this->customer)->willReturn(null);
        $this->blikAliasFactory->createForCustomer($this->customer)->willReturn($blikAlias);

        $result = (new BlikAliasResolver(
            $this->blikAliasRepository->reveal(),
            $this->blikAliasFactory->reveal(),
        ))->resolve($this->customer->reveal());

        $this->assertSame($blikAlias, $result);
    }
}
