<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

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

        /** @var array{tpay?: array{pay_by_link_channel_id?: string}} $paymentDetails */
        $paymentDetails = $payment->getDetails();
        $bankGroupId = $paymentDetails['tpay']['pay_by_link_channel_id']
            ?? throw new \InvalidArgumentException('The given payment does not have a bank selected.');

        $payload['pay']['channelId'] = (int) $bankGroupId;

        return $payload;
    }
}
