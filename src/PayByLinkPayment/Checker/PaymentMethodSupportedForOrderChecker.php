<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker;

use CommerceWeavers\SyliusTpayPlugin\Tpay\GatewayName;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProviderInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodSupportedForOrderChecker implements PaymentMethodSupportedForOrderCheckerInterface
{
    public function __construct(
        private readonly CypherInterface $cypher,
        private readonly OrderAwareValidTpayChannelListProviderInterface $orderAwareValidTpayChannelListProvider,
    ) {
    }

    public function isSupportedForOrder(PaymentMethodInterface $paymentMethod, OrderInterface $order): bool
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (null === $gatewayConfig || GatewayName::PAY_BY_LINK !== $gatewayConfig->getFactoryName()) {
            return true;
        }

        if ($gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->decrypt($this->cypher);
        }

        $tpayChannelId = $gatewayConfig->getConfig()['tpay_channel_id'] ?? null;

        if (null === $tpayChannelId) {
            return true;
        }

        $validTpayChannelList = $this->orderAwareValidTpayChannelListProvider->provide($order);

        return isset($validTpayChannelList[$tpayChannelId]);
    }
}
