<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use Sylius\Component\Core\Model\OrderInterface;

final class OrderAwareValidTpayChannelListProvider implements OrderAwareValidTpayChannelListProviderInterface
{
    private const FLOAT_AMOUNT_VALUE_TO_INT_MULTIPLIER = 100;

    public function __construct(private readonly ValidTpayChannelListProviderInterface $validTpayChannelListProvider)
    {
    }

    public function provide(OrderInterface $order): array
    {
        $orderTotal = $order->getTotal();
        $channelList = $this->validTpayChannelListProvider->provide();

        foreach ($channelList as $key => $channel) {
            foreach ($channel['constraints'] ?? [] as $constraint) {
                $constraintField = $constraint['field'] ?? null;
                $constraintValue = $constraint['value'] ?? null;
                $constraintType = $constraint['type'] ?? null;

                if ('amount' !== $constraintField || null === $constraintValue || null === $constraintType) {
                    continue;
                }

                $constraintIntValue = (int) ($constraintValue * self::FLOAT_AMOUNT_VALUE_TO_INT_MULTIPLIER);

                if (
                    ('min' === $constraintType && $orderTotal < $constraintIntValue) ||
                    ('max' === $constraintType && $orderTotal > $constraintIntValue)
                ) {
                    unset($channelList[$key]);

                    break;
                }
            }
        }

        return $channelList;
    }
}
