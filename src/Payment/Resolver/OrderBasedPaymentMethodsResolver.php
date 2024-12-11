<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payment\Resolver;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker\PaymentMethodSupportedForOrderCheckerInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webmozart\Assert\Assert;

final class OrderBasedPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    public function __construct(
        private readonly PaymentMethodsResolverInterface $paymentMethodsResolver,
        private readonly PaymentMethodSupportedForOrderCheckerInterface $paymentMethodSupportedForOrderChecker,
    ) {
    }

    public function getSupportedMethods(PaymentInterface $subject): array
    {
        Assert::isInstanceOf($subject, CorePaymentInterface::class);
        Assert::true($this->supports($subject), 'This payment method is not support by resolver');

        /** @var PaymentMethodInterface[] $supportedMethods */
        $supportedMethods = $this->paymentMethodsResolver->getSupportedMethods($subject);

        foreach ($supportedMethods as $key => $supportedMethod) {
            $order = $subject->getOrder();
            Assert::notNull($order);

            if ($this->paymentMethodSupportedForOrderChecker->isSupportedForOrder($supportedMethod, $order)) {
                continue;
            }

            unset($supportedMethods[$key]);
        }

        return array_values($supportedMethods);
    }

    public function supports(PaymentInterface $subject): bool
    {
        return $this->paymentMethodsResolver->supports($subject);
    }
}
