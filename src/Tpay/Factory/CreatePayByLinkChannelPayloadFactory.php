<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\Exception\BankNotSelectedException;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreatePayByLinkChannelPayloadFactory implements CreatePayByLinkChannelPayloadFactoryInterface
{
    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        $gatewayConfig = $payment->getMethod()?->getGatewayConfig()?->getConfig();
        $channelId = $gatewayConfig['tpay_channel_id'] ?? throw BankNotSelectedException::create();

        $payload['pay']['channelId'] = (int) $channelId;

        return $payload;
    }
}
