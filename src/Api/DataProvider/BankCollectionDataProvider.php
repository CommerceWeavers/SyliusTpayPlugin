<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\Bank;

final class BankCollectionDataProvider implements CollectionDataProviderInterface
{
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        // we get these from the provider
        $banks = [
            'ing' => 'ING Bank Śląski',
            'mBank' => 'mBank',
        ];

        foreach ($banks as $id => $name) {
            yield new Bank($id, $name);
        }
    }
}
