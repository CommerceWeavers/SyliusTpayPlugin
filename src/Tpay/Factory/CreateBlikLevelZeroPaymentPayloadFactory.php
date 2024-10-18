<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Resolver\BlikAliasResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PayGroup;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class CreateBlikLevelZeroPaymentPayloadFactory implements CreateBlikLevelZeroPaymentPayloadFactoryInterface
{
    public function __construct(
        private readonly CreateRedirectBasedPaymentPayloadFactoryInterface $createRedirectBasedPaymentPayloadFactory,
        private readonly ChannelContextInterface $channelContext,
        private readonly BlikAliasResolverInterface $blikAliasResolver,
        private readonly ObjectManager $blikAliasManager,
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

        if (null !== $blikToken) {
            $payload['pay']['blikPaymentData']['blikToken'] = $blikToken;
        }

        if (!$blikSaveAlias && !$blikUseAlias) {
            return $payload;
        }

        /** @var CustomerInterface $customer */
        $customer = $payment->getOrder()?->getCustomer() ?? throw new \InvalidArgumentException('The customer is missing.');
        $blikAlias = $this->blikAliasResolver->resolve($customer);

        if ($blikSaveAlias) {
            $blikAlias->redefine();
        }

        $payload['pay']['blikPaymentData']['aliases'] = [
            'value' => $blikAlias->getValue(),
            'type' => 'UID',
            'label' => $this->channelContext->getChannel()->getName(),
        ];

        // $this->blikAliasManager->persist($blikAlias); FIXME: The EntityManager is closed.

        return $payload;
    }
}
