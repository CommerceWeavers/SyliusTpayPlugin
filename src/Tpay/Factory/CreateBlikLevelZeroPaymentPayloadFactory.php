<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateBlikLevelZeroPaymentPayloadFactory implements CreateBlikLevelZeroPaymentPayloadFactoryInterface
{
    public function __construct(
        private CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        /** @var array{tpay?: array{blik_token?: string}} $paymentDetails */
        $paymentDetails = $payment->getDetails();
        $blikToken = $paymentDetails['tpay']['blik_token'] ?? throw new \InvalidArgumentException('The given payment does not have a blik code.');

        $payload['pay']['groupId'] = PayGroup::BLIK;
        $payload['pay']['blikPaymentData'] = [];
        $payload['pay']['blikPaymentData']['blikToken'] = $blikToken;

        return $payload;
    }
}
