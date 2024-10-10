<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\UnableToGetBankListException;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;

final class TpayApiChannelListProvider implements TpayApiChannelListProviderInterface
{
    public function __construct(
        private readonly TpayTransactionChannelResolverInterface $channelResolver,
    ) {
    }

    public function provide(): array
    {
        $result = $this->channelResolver->resolve();

        if (!isset($result['result']) || 'success' !== $result['result']) {
            throw new UnableToGetBankListException('Unable to get channels list. Response: ' . json_encode($result));
        }

        return $result['channels'];
    }
}
