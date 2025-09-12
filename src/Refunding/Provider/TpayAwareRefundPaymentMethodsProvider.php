<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Refunding\Provider;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;

final class TpayAwareRefundPaymentMethodsProvider implements RefundPaymentMethodsProviderInterface
{
    public function __construct(
        private readonly RefundPaymentMethodsProviderInterface $inner,
    ) {
    }

    /** @return PaymentMethodInterface[] */
    public function findForOrder(OrderInterface $order): array
    {
        $paymentMethods = $this->inner->findForOrder($order);

        $lastCompletedPayment = $this->getLastCompletedPayment($order);

        if (null === $lastCompletedPayment) {
            return $paymentMethods;
        }

        $details = $lastCompletedPayment->getDetails();
        $paymentDetails = PaymentDetails::fromArray($details);

        if (null !== $paymentDetails->getTransactionId()) {
            return $paymentMethods;
        }

        return array_values(array_filter($paymentMethods, function (PaymentMethodInterface $method): bool {
            $factoryName = $method->getGatewayConfig()?->getFactoryName();

            return $factoryName !== 'tpay_redirect';
        }));
    }

    private function getLastCompletedPayment(OrderInterface $order): ?PaymentInterface
    {
        return $order->getLastPayment(PaymentInterface::STATE_COMPLETED);
    }
}
