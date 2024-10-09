<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\Bank;

final class BankItemDataProvider implements ItemDataProviderInterface
{
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // we get these from the provider
        $banks = [
            'ing' => 'ING Bank Śląski',
            'mBank' => 'mBank',
        ];

        if (!isset($banks[$id])) {
            return null;
        }

        return new Bank($id, $banks[$id]);
    }
}
