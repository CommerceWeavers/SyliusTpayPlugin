<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Provider;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderAwareValidTpayChannelListProviderInterface
{
    public function provide(OrderInterface $order): array;
}
