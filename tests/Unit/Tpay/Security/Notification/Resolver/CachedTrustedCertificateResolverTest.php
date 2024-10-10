<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Security\Notification\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CachedTrustedCertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\Cache\CacheInterface;

final class CachedTrustedCertificateResolverTest extends TestCase
{
    use ProphecyTrait;

    private CacheInterface|ObjectProphecy $cache;

    private TrustedCertificateResolverInterface|ObjectProphecy $decorated;

    protected function setUp(): void
    {
        $this->cache = $this->prophesize(CacheInterface::class);
        $this->decorated = $this->prophesize(TrustedCertificateResolverInterface::class);
    }

    public function test_it_caches_trusted_certificate(): void
    {
        $this->cache
            ->get('commerce_weavers_sylius_tpay_trusted_certificate', Argument::type('callable'))
            ->shouldBeCalled()
            ->willReturn('trusted_certificate')
        ;

        $result = $this->createTestSubject()->resolve();

        $this->assertSame('trusted_certificate', $result);
    }

    public function test_it_throws_an_exception_if_resolved_certificate_is_not_a_string(): void
    {
        $this->cache
            ->get('commerce_weavers_sylius_tpay_trusted_certificate', Argument::type('callable'))
            ->shouldBeCalled()
            ->willReturn(42)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Certificate must be a string');

        $this->createTestSubject()->resolve();
    }

    private function createTestSubject(): TrustedCertificateResolverInterface
    {
        return new CachedTrustedCertificateResolver($this->cache->reveal(), $this->decorated->reveal(), 300);
    }
}
