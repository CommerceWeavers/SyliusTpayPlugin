<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\ContextProvider;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory as PayByLinkGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\ValidTpayChannelListProviderInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class BankListContextProvider implements ContextProviderInterface
{
    private const SUPPORTED_TEMPLATE_BLOCK_EVENT_NAMES = [
        'sylius.shop.checkout.complete.summary',
        'cw.tpay.shop.select_payment.choice_item_form',
    ];

    public function __construct(
        private readonly ValidTpayChannelListProviderInterface $validTpayChannelListProvider,
        private readonly OrderAwareValidTpayChannelListProviderInterface $orderAwareValidTpayChannelListProvider,
        private readonly CypherInterface $cypher,
    ) {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        $templateContext['defaultTpayChannelId'] = null;
        $templateContext['banks'] = [];

        $paymentMethod = $this->resolvePaymentMethod($templateContext);
        $gatewayConfig = $paymentMethod?->getGatewayConfig();

        if (
            null === $paymentMethod ||
            null === $gatewayConfig ||
            PayByLinkGatewayFactory::NAME !== $gatewayConfig->getFactoryName()
        ) {
            return $templateContext;
        }

        $decryptedGatewayConfig = $this->getEncryptedGatewayConfig($gatewayConfig);

        /**
         * @phpstan-ignore-next-line
         *
         * @var string|null $tpayChannelId
         */
        $tpayChannelId = $decryptedGatewayConfig['tpay_channel_id'] ?? null;

        $templateContext['defaultTpayChannelId'] = $tpayChannelId;

        if (null === $tpayChannelId) {
            /** @var OrderInterface|null $order */
            $order = $templateContext['order'] ?? null;

            $templateContext['banks'] = null === $order
                ? $this->validTpayChannelListProvider->provide()
                : $this->orderAwareValidTpayChannelListProvider->provide($order)
            ;
        }

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return
            'pay_by_link' === $templateBlock->getName() &&
            in_array($templateBlock->getEventName(), self::SUPPORTED_TEMPLATE_BLOCK_EVENT_NAMES, true)
        ;
    }

    private function resolvePaymentMethod(array $templateContext): ?PaymentMethodInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $templateContext['method'] ?? null;
        if (null !== $paymentMethod) {
            return $paymentMethod;
        }

        /** @var OrderInterface|null $order */
        $order = $templateContext['order'] ?? null;
        if (null === $order) {
            return null;
        }

        $payment = $order->getLastPayment();
        if (null === $payment) {
            return null;
        }

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment->getMethod();

        return $paymentMethod;
    }

    private function getEncryptedGatewayConfig(GatewayConfigInterface $gatewayConfig): array
    {
        if ($gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->decrypt($this->cypher);
        }

        return $gatewayConfig->getConfig();
    }
}
