<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedCertificateResolver implements CertificateResolverInterface
{
    public const COMMERCE_WEAVERS_SYLIUS_TPAY_CERTIFICATE = 'commerce_weavers_sylius_tpay_certificate';

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly CertificateResolverInterface $decorated,
        private readonly int $cacheTtlInSeconds,
    ) {
    }

    public function resolve(string $x5u): string
    {
        $certificate = $this->cache->get(self::COMMERCE_WEAVERS_SYLIUS_TPAY_CERTIFICATE, function (ItemInterface $item) use ($x5u) {
            $item->expiresAfter($this->cacheTtlInSeconds);

            return $this->decorated->resolve($x5u);
        });

        if (!is_string($certificate)) {
            throw new \RuntimeException('Certificate must be a string');
        }

        return $certificate;
    }
}
