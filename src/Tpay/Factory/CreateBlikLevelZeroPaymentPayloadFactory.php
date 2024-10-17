<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Repository\BlikAliasRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Uid\Uuid;

final class CreateBlikLevelZeroPaymentPayloadFactory implements CreateBlikLevelZeroPaymentPayloadFactoryInterface
{
    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
        private readonly ChannelContextInterface $channelContext,
        private readonly BlikAliasRepositoryInterface $blikAliasRepository,
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
        $blikToken = $paymentDetails['tpay']['blik_token'] ?? null;
        $blikSaveAlias = (bool) ($paymentDetails['tpay']['blik_save_alias'] ?? false);
        $blikUseAlias = (bool) ($paymentDetails['tpay']['blik_use_alias'] ?? false);

        if (null === $blikToken && !$blikUseAlias) {
            throw new \InvalidArgumentException('The given payment does not have a blik code.');
        }

        $payload['pay']['groupId'] = PayGroup::BLIK;
        $payload['pay']['blikPaymentData'] = [];

        if (!$blikUseAlias) {
            $payload['pay']['blikPaymentData']['blikToken'] = $blikToken;
        }

        $payload['pay']['blikPaymentData']['aliases'] = [
            'value' => $blikSaveAlias ? $this->generateBlikAliasValue() : $this->resolveBlikAliasValue($payment),
            'type' => 'UID',
            'label' => $this->channelContext->getChannel()->getName(),
        ];

        return $payload;
    }

    private function generateBlikAliasValue(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    private function resolveBlikAliasValue(PaymentInterface $payment): string
    {
        /** @var CustomerInterface $customer */
        $customer = $payment->getOrder()?->getCustomer() ?? throw new \InvalidArgumentException('The customer is missing.');

        return (string) $this->blikAliasRepository->findOneByCustomer($customer)?->getValue();
    }
}
