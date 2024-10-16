<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Uid\Uuid;

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
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array
    {
        /** @var array{pay: array<string, mixed>} $payload */
        $payload = $this->createRedirectBasedPaymentPayloadFactory->createFrom($payment, $notifyUrl, $localeCode);

        /** @var array{tpay?: array{blik_token?: string, blik_save_alias?: bool}} $paymentDetails */
        $paymentDetails = $payment->getDetails();
        $blikToken = $paymentDetails['tpay']['blik_token'] ?? throw new \InvalidArgumentException('The given payment does not have a blik code.');
        $blikSaveAlias = (bool) ($paymentDetails['tpay']['blik_save_alias'] ?? false);

        $payload['pay']['groupId'] = PayGroup::BLIK;
        $payload['pay']['blikPaymentData'] = [];
        $payload['pay']['blikPaymentData']['blikToken'] = $blikToken;

        if ($blikSaveAlias) {
            $payload['pay']['blikPaymentData']['aliases'] = [
                'value' => Uuid::v4()->toRfc4122(),
                'type' => 'UID',
                'label' => $this->channelContext->getChannel()->getName(),
            ];
        }

        return $payload;
    }
}
