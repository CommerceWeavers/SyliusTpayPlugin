<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;

final class TpayChannelItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $apiBankListProvider,
    ) {
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?TpayChannel
    {
        $transactionChannels = $this->apiBankListProvider->provide();

        /** @var array $transactionChannel */
        foreach ($transactionChannels as $transactionChannel) {
            if ($transactionChannel['id'] === $id) {
                return TpayChannel::FromArray($transactionChannel);
            }
        }

        return null;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TpayChannel::class === $resourceClass;
    }
}
