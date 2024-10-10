<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;

final class TpayChannelCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $apiBankListProvider,
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $transactionChannels = $this->apiBankListProvider->provide();

        foreach ($transactionChannels as $transactionChannel) {
            yield TpayChannel::FromArray($transactionChannel);
        }
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TpayChannel::class === $resourceClass;
    }
}
