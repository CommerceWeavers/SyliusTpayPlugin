<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayBank;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;

final class TpayBankItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $apiBankListProvider
    ) {
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?TpayBank
    {
        $transactionChannels = $this->apiBankListProvider->provide();

        /** @var TpayBank $transactionChannel */
        foreach ($transactionChannels as $transactionChannel) {
            if ($transactionChannel->getId() === $id) {
                return TpayBank::FromArray($transactionChannel);
            }
        }

        return null;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TpayBank::class === $resourceClass;
    }
}
