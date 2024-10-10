<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayBank;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;

final class TpayBankCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $apiBankListProvider
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $transactionChannels = $this->apiBankListProvider->provide();

        foreach ($transactionChannels as $transactionChannel) {
            yield TpayBank::FromArray($transactionChannel);
        }
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TpayBank::class === $resourceClass;
    }
}
