<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\X509FactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\Exception\InvalidSignatureException;
use phpseclib3\Crypt\RSA;

final class SignatureVerifier implements SignatureVerifierInterface
{
    public function __construct(
        private readonly CertificateResolverInterface $certificateResolver,
        private readonly TrustedCertificateResolverInterface $trustedCertificateResolver,
        private readonly X509FactoryInterface $x509Factory,
    ) {
    }

    public function verify(string $jws, string $requestContent): bool
    {
        $jwsData = explode('.', $jws);
        $headers = $jwsData[0] ?? null;
        $signature = $jwsData[2] ?? null;

        if (null === $headers || null === $signature) {
            throw new InvalidSignatureException('Invalid JWS format');
        }

        $headersJson = base64_decode(strtr($headers, '-_', '+/'));

        /** @var array $headersData */
        $headersData = json_decode($headersJson, true);

        /** @var string|null $x5u */
        $x5u = $headersData['x5u'] ?? null;

        if (null === $x5u) {
            throw new InvalidSignatureException('Missing x5u header');
        }

        $certificate = $this->certificateResolver->resolve($x5u);
        $trusted = $this->trustedCertificateResolver->resolve();

        $x509 = $this->x509Factory->create();
        $x509->loadX509($certificate);
        $x509->loadCA($trusted);

        if (!$x509->validateSignature()) {
            return false;
        }

        $payload = str_replace('=', '', strtr(base64_encode($requestContent), '+/', '-_'));
        $decodedSignature = base64_decode(strtr($signature, '-_', '+/'));
        $publicKey = $x509->getPublicKey();
        $publicKey = $x509->withSettings($publicKey, 'sha256', RSA::SIGNATURE_PKCS1);

        if (!$publicKey->verify($headers . '.' . $payload, $decodedSignature)) {
            return false;
        }

        return true;
    }
}
