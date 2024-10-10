<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Webmozart\Assert\Assert;

final class CachedTpayTransactionChannelResolver implements TpayTransactionChannelResolverInterface
{
    public const COMMERCE_WEAVERS_SYLIUS_TPAY_TRANSACTION_CHANNELS = 'commerce_wavers_tpay_transaction_channels';

    public function __construct(
        private readonly TpayTransactionChannelResolver $decorated,
        private readonly CacheInterface $cache,
        private readonly int $cacheTtlInSeconds,
    ) {
    }

    public function resolve(): array
    {
        $result = $this->cache->get(self::COMMERCE_WEAVERS_SYLIUS_TPAY_TRANSACTION_CHANNELS, function (ItemInterface $item): array {
            $item->expiresAfter($this->cacheTtlInSeconds);

            return $this->decorated->resolve();
        });

        Assert::isArray($result);

        return $result;
    }
}
