<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use Sylius\Component\Core\Model\PaymentInterface;

final class CreateGooglePayPaymentPayloadFactory implements CreateGooglePayPaymentPayloadFactoryInterface
{
    private const GOOGLE_PAY_GROUP_ID = 166;

    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
    ) {
    }

    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        /** @var array{tpay?: array{google_pay_token?: string}} $paymentDetails */
        $paymentDetails = $payment->getDetails();
        $googlePayToken = $paymentDetails['tpay']['google_pay_token'] ?? throw new \InvalidArgumentException('The given payment does not have a Google Pay token.');

        $payload['pay']['groupId'] = self::GOOGLE_PAY_GROUP_ID;
        $payload['pay']['googlePayPaymentData'] = $googlePayToken;

        return $payload;
    }
}
