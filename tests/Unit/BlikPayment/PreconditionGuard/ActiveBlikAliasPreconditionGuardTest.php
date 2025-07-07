<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\BlikPayment\PreconditionGuard;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\ActiveBlikAliasPreconditionGuard;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasExpiredException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasNotRegisteredException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Clock\ClockInterface;

final class ActiveBlikAliasPreconditionGuardTest extends TestCase
{
    use ProphecyTrait;

    private ClockInterface|ObjectProphecy $clock;

    protected function setUp(): void
    {
        $this->clock = $this->prophesize(ClockInterface::class);
    }

    public function test_it_throws_exception_if_blik_alias_is_not_registered(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $blikAlias->isRegistered()->willReturn(false);

        $this->expectException(BlikAliasNotRegisteredException::class);

        $this->createTestSubject()->denyIfNotActive($blikAlias->reveal());
    }

    public function test_it_throws_exception_if_blik_alias_is_expired(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $blikAlias->isRegistered()->willReturn(true);
        $blikAlias->getExpirationDate()->willReturn(new \DateTimeImmutable('yesterday'));
        $this->clock->now()->willReturn(new \DateTimeImmutable('today'));

        $this->expectException(BlikAliasExpiredException::class);

        $this->createTestSubject()->denyIfNotActive($blikAlias->reveal());
    }

    public function test_it_does_nothing_if_blik_alias_is_registered_and_not_expired(): void
    {
        $blikAlias = $this->prophesize(BlikAliasInterface::class);
        $blikAlias->isRegistered()->willReturn(true);
        $blikAlias->getExpirationDate()->willReturn(new \DateTimeImmutable('tomorrow'));
        $this->clock->now()->willReturn(new \DateTimeImmutable('today'));

        $this->expectNotToPerformAssertions();

        $this->createTestSubject()->denyIfNotActive($blikAlias->reveal());
    }

    private function createTestSubject(): ActiveBlikAliasPreconditionGuard
    {
        return new ActiveBlikAliasPreconditionGuard($this->clock->reveal());
    }
}
