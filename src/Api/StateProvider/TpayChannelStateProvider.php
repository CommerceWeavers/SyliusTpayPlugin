<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\StateProvider;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;

final class TpayChannelStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly TpayTransactionChannelResolverInterface $tpayTransactionChannelResolver,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $transactionChannels = $this->tpayTransactionChannelResolver->resolve();

        if ($operation instanceof CollectionOperationInterface) {
            $channels = [];
            foreach ($transactionChannels as $transactionChannel) {
                $channels[] = TpayChannel::fromArray($transactionChannel);
            }

            return $channels;
        }

        $id = $uriVariables['id'] ?? null;

        if ($id === null) {
            return null;
        }

        if (is_string($id) || is_int($id)) {
            if (array_key_exists($id, $transactionChannels)) {
                return TpayChannel::fromArray($transactionChannels[$id]);
            }
        }

        return null;
    }
}
