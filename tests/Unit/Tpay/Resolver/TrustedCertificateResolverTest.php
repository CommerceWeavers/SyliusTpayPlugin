<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class TrustedCertificateResolverTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_resolves_certificate(): void
    {
        $testedClass = new TrustedCertificateResolver();

        $result = $testedClass->resolve(false);

        $this->assertStringContainsString('BEGIN CERTIFICATE', $result);
        $this->assertStringContainsString('END CERTIFICATE', $result);
    }
}
