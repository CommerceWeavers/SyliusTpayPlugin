<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;

final class TpayChannelItemDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly TpayTransactionChannelResolverInterface $tpayTransactionChannelResolver,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $transactionChannels = $this->tpayTransactionChannelResolver->resolve();

        $id = $uriVariables['id'] ?? null;
        if ($id !== null && array_key_exists($id, $transactionChannels)) {
            return TpayChannel::fromArray($transactionChannels[$id]);
        }

        return null;
    }
}
