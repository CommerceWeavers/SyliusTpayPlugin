<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Security\Notification\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CachedCertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CertificateResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Contracts\Cache\CacheInterface;

final class CachedCertificateResolverTest extends TestCase
{
    use ProphecyTrait;

    private CacheInterface|ObjectProphecy $cache;

    private CertificateResolverInterface|ObjectProphecy $decorated;

    protected function setUp(): void
    {
        $this->cache = $this->prophesize(CacheInterface::class);
        $this->decorated = $this->prophesize(CertificateResolverInterface::class);
    }

    public function test_it_caches_certificate(): void
    {
        $this->cache
            ->get('commerce_weavers_sylius_tpay_certificate', Argument::type('callable'))
            ->shouldBeCalled()
            ->willReturn('certificate')
        ;

        $result = $this->createTestSubject()->resolve('x5u');

        $this->assertSame('certificate', $result);
    }

    public function test_it_throws_an_exception_if_resolved_certificate_is_not_a_string(): void
    {
        $this->cache
            ->get('commerce_weavers_sylius_tpay_certificate', Argument::type('callable'))
            ->shouldBeCalled()
            ->willReturn(42)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Certificate must be a string');

        $this->createTestSubject()->resolve('x5u');
    }

    private function createTestSubject(): CertificateResolverInterface
    {
        return new CachedCertificateResolver($this->cache->reveal(), $this->decorated->reveal(), 300);
    }
}
