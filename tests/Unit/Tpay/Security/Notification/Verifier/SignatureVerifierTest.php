<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Security\Notification\Verifier;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Checker\ProductionModeCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\X509FactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\Exception\InvalidSignatureException;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifier;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;
use phpseclib3\Crypt\Common\PublicKey;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Tpay\OpenApi\Utilities\phpseclib\Crypt\RSA;
use Tpay\OpenApi\Utilities\phpseclib\File\X509;

final class SignatureVerifierTest extends TestCase
{
    use ProphecyTrait;

    private CertificateResolverInterface|ObjectProphecy $certificateResolver;

    private TrustedCertificateResolverInterface|ObjectProphecy $trustedCertificateResolver;

    private X509FactoryInterface|ObjectProphecy $x509Factory;

    private ProductionModeCheckerInterface|ObjectProphecy $productionModeChecker;

    private X509|ObjectProphecy $x509;

    private PublicKey|ObjectProphecy $publicKey;

    protected function setUp(): void
    {
        $this->certificateResolver = $this->prophesize(CertificateResolverInterface::class);
        $this->trustedCertificateResolver = $this->prophesize(TrustedCertificateResolverInterface::class);
        $this->x509Factory = $this->prophesize(X509FactoryInterface::class);
        $this->productionModeChecker = $this->prophesize(ProductionModeCheckerInterface::class);
        $this->x509 = $this->prophesize(X509::class);
        $this->publicKey = $this->prophesize(PublicKey::class);

        $this->x509Factory->create()->willReturn($this->x509->reveal());

        $this->x509->getPublicKey()->willReturn($this->publicKey->reveal());
    }

    public function test_it_throws_an_exception_when_jws_header_is_missing(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Invalid JWS format');

        $this->createTestSubject()->verify('', 'requestContent');
    }

    public function test_it_throws_an_exception_when_signature_is_missing(): void
    {
        $this->expectException(InvalidSignatureException::class);
        $this->expectExceptionMessage('Invalid JWS format');

        $this->createTestSubject()->verify('headers.payload', 'requestContent');
    }

    public function test_it_returns_true_when_jws_is_valid(): void
    {
        $header = base64_encode(json_encode(['x5u' => 'https://cw.x5u']));
        $encodedRequestContent = str_replace('=', '', strtr(base64_encode('request content'), '+/', '-_'));
        $signature = base64_encode('sigmature');

        $jws = sprintf('%s.non_used_value.%s', $header, $signature);

        $this->productionModeChecker->isProduction('https://cw.x5u')->willReturn(true);
        $this->certificateResolver->resolve('https://cw.x5u')->willReturn('cert');
        $this->trustedCertificateResolver->resolve(true)->willReturn('trusted_cert');

        $this->x509->loadX509('cert')->shouldBeCalled();
        $this->x509->loadCA('trusted_cert')->shouldBeCalled();
        $this->x509->validateSignature()->willReturn(true);
        $this->x509->withSettings($this->publicKey, 'sha256', RSA::SIGNATURE_PKCS1)->willReturn($this->publicKey);

        $this->publicKey->verify(sprintf('%s.%s', $header, $encodedRequestContent), 'sigmature')->willReturn(true);

        $isJwsValid = $this->createTestSubject()->verify($jws, 'request content');

        $this->assertTrue($isJwsValid);
    }

    public function test_it_returns_false_when_x509_signature_is_invalid(): void
    {
        $header = base64_encode(json_encode(['x5u' => 'https://cw.x5u']));
        $signature = base64_encode('sigmature');

        $jws = sprintf('%s.non_used_value.%s', $header, $signature);

        $this->productionModeChecker->isProduction('https://cw.x5u')->willReturn(true);
        $this->certificateResolver->resolve('https://cw.x5u')->willReturn('cert');
        $this->trustedCertificateResolver->resolve(true)->willReturn('trusted_cert');

        $this->x509->loadX509('cert')->shouldBeCalled();
        $this->x509->loadCA('trusted_cert')->shouldBeCalled();
        $this->x509->validateSignature()->willReturn(false);

        $isJwsValid = $this->createTestSubject()->verify($jws, 'request content');

        $this->assertFalse($isJwsValid);
    }

    public function test_it_returns_false_when_jws_is_invalid(): void
    {
        $header = base64_encode(json_encode(['x5u' => 'https://cw.x5u']));
        $encodedRequestContent = str_replace('=', '', strtr(base64_encode('request content'), '+/', '-_'));
        $signature = base64_encode('sigmature');

        $jws = sprintf('%s.non_used_value.%s', $header, $signature);

        $this->productionModeChecker->isProduction('https://cw.x5u')->willReturn(true);
        $this->certificateResolver->resolve('https://cw.x5u')->willReturn('cert');
        $this->trustedCertificateResolver->resolve(true)->willReturn('trusted_cert');

        $this->x509->loadX509('cert')->shouldBeCalled();
        $this->x509->loadCA('trusted_cert')->shouldBeCalled();
        $this->x509->validateSignature()->willReturn(true);
        $this->x509->withSettings($this->publicKey, 'sha256', RSA::SIGNATURE_PKCS1)->willReturn($this->publicKey);

        $this->publicKey->verify(sprintf('%s.%s', $header, $encodedRequestContent), 'sigmature')->willReturn(false);

        $isJwsValid = $this->createTestSubject()->verify($jws, 'request content');

        $this->assertFalse($isJwsValid);
    }

    private function createTestSubject(): SignatureVerifierInterface
    {
        return new SignatureVerifier(
            $this->certificateResolver->reveal(),
            $this->trustedCertificateResolver->reveal(),
            $this->x509Factory->reveal(),
            $this->productionModeChecker->reveal(),
        );
    }
}
