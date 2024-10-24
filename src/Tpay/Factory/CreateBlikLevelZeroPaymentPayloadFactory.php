<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateBlikLevelZeroPaymentPayloadFactory implements CreateBlikLevelZeroPaymentPayloadFactoryInterface
{
    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
        private readonly ChannelContextInterface $channelContext,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createFrom(PaymentInterface $payment, ?BlikAliasInterface $blikAlias, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        /** @var array{tpay?: array} $paymentDetails */
        $paymentDetails = $payment->getDetails();
        $blikToken = $paymentDetails['tpay']['blik_token'] ?? null;

        $payload['pay']['groupId'] = PayGroup::BLIK;
        $payload['pay']['blikPaymentData'] = [];

        if (null !== $blikToken) {
            $payload['pay']['blikPaymentData']['blikToken'] = $blikToken;
        }

        if (null !== $blikAlias) {
            $payload['pay']['blikPaymentData']['aliases'] = [
                'value' => $blikAlias->getValue(),
                'type' => 'UID',
                'label' => $this->channelContext->getChannel()->getName(),
            ];
        }

        return $payload;
    }
}
