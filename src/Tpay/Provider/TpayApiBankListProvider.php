<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\UnableToGetBankListException;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;

final class TpayApiBankListProvider implements TpayApiBankListProviderInterface
{
    public function __construct(
        private readonly TpayTransactionChannelResolverInterface $channelResolver,
    ) {
    }

    public function provide(): array
    {
        $result = $this->channelResolver->resolve();

        if (!isset($result['result']) || 'success' !== $result['result']) {
            throw new UnableToGetBankListException('Unable to get banks list. Response: ' . json_encode($result));
        }

        return array_filter($result['channels'], static function (array $channel) {
            return
                ($channel['instantRedirection'] ?? false) === true &&
                ($channel['onlinePayment'] ?? false) === true &&
                // TODO should we show non available ones as well?
                ($channel['available'] ?? false) === true
            ;
        });
    }
}
