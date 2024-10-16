<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\ContextProvider;

use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class BankListContextProvider implements ContextProviderInterface
{
    public function __construct(
        private readonly TpayApiBankListProviderInterface $bankListProvider,
    ) {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        /** @var (OrderInterface&OrderLastNewPaymentAwareInterface)|null $order */
        $order = $templateContext['order'] ?? null;
        Assert::isInstanceOf($order, OrderInterface::class);
        /** @var PaymentInterface|null $payment */
        $payment = $order->getLastCartPayment();
        Assert::isInstanceOf($payment, PaymentInterface::class);
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        if (($paymentMethod->getGatewayConfig()?->getConfig()['type'] ?? null) === PaymentType::PAY_BY_LINK) {
            $templateContext['banks'] = $this->bankListProvider->provide();
        }

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return 'sylius.shop.checkout.complete.summary' === $templateBlock->getEventName() &&
            'pay_by_link' === $templateBlock->getName()
        ;
    }
}
