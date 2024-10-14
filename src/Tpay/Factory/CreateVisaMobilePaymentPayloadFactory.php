<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class CreateVisaMobilePaymentPayloadFactory implements CreateVisaMobilePaymentPayloadFactoryInterface
{
    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
    ) {
    }

    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        /** @var array{tpay?: array{visa_mobile?: string}} $paymentDetails */
        $paymentDetails = $payment->getDetails();

        Assert::keyExists($paymentDetails['tpay'], 'visa_mobile',
            'The given payment is not visa mobile payment type.'
        );

        $payload['pay']['groupId'] = PayGroup::VISA_MOBILE;

        return $payload;
    }
}
