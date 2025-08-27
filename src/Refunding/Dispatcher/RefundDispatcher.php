<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityCheckerInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Webmozart\Assert\Assert;

final class RefundDispatcher implements RefundDispatcherInterface
{
    public function __construct(
        private readonly Payum $payum,
        private readonly RefundDispatchEligibilityCheckerInterface $eligibilityChecker,
    ) {
    }

    public function dispatch(PaymentInterface|RefundPaymentInterface $payment): void
    {
        if (!$this->eligibilityChecker->isEligible($payment)) {
            return;
        }

        $this->getGateway($payment)->execute(new Refund($payment));
    }

    private function getGateway(PaymentInterface|RefundPaymentInterface $payment): GatewayInterface
    {
        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $payment instanceof PaymentInterface ? $payment->getMethod() : $payment->getPaymentMethod();
        $gatewayName = $paymentMethod?->getGatewayConfig()?->getFactoryName();

        Assert::notNull($gatewayName);

        return $this->payum->getGateway($gatewayName);
    }
}
