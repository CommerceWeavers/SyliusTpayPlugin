<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreatePayByLinkPayloadFactory implements CreatePayByLinkPayloadFactoryInterface
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

        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $payByLinkChannelId = $paymentDetails->getPayByLinkChannelId()
            ?? throw new \InvalidArgumentException('The given payment does not have a bank selected.');

        $payload['pay']['channelId'] = (int) $payByLinkChannelId;

        return $payload;
    }
}
