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
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());
        $payByLinkChannelId = $paymentDetails->getTpayChannelId();

        if (null === $payByLinkChannelId || '' === $payByLinkChannelId) {
            // No bank selected - use Paywall redirect (user will select bank on Tpay's interface)
            return $payload;
        }

        // Bank selected - add channelId to payload for direct bank redirection
        $payload['pay'] = ['channelId' => (int) $payByLinkChannelId];

        return $payload;
    }
}
