<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
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

        $paymentDetails = PaymentDetails::fromArray($payment->getDetails());

        Assert::notNull(
            $visaMobilePhoneNumber = $paymentDetails->getVisaMobilePhoneNumber(),
            'The given payment has no visa mobile phone number.',
        );

        $payload['payer']['phone'] = $visaMobilePhoneNumber;
        $payload['pay']['groupId'] = PayGroup::VISA_MOBILE;

        return $payload;
    }
}
