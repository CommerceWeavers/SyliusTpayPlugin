<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;

final class TpayChannelCollectionDataProvider implements ProviderInterface
{
    public function __construct(
        private readonly TpayTransactionChannelResolverInterface $tpayTransactionChannelResolver,
    ) {
    }

    /**
     * @return array<TpayChannel>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $transactionChannels = $this->tpayTransactionChannelResolver->resolve();

        $channels = [];
        foreach ($transactionChannels as $transactionChannel) {
            $channels[] = TpayChannel::fromArray($transactionChannel);
        }

        return $channels;
    }
}
