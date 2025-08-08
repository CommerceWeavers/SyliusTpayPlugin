<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Refunding\Checker;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;

final readonly class RefundDispatchEligibilityChecker implements RefundDispatchEligibilityCheckerInterface
{
    public function __construct(
        private RefundPluginAvailabilityCheckerInterface $refundPluginAvailabilityChecker,
    ) {
    }

    public function isEligible(PaymentInterface|RefundPaymentInterface $payment): bool
    {
        $factoryName = $payment instanceof PaymentInterface
            ? $payment->getMethod()?->getGatewayConfig()?->getFactoryName()
            : $payment->getPaymentMethod()->getGatewayConfig()?->getFactoryName();

        if ($factoryName === null || !str_starts_with($factoryName, 'tpay_')) {
            return false;
        }

        $isRefundPluginAvailable = $this->refundPluginAvailabilityChecker->isAvailable();
        $isPayment = $payment instanceof PaymentInterface;
        $isRefundPayment = $payment instanceof RefundPaymentInterface;

        return (!$isRefundPluginAvailable && $isPayment) || ($isRefundPluginAvailable && $isRefundPayment);
    }
}
