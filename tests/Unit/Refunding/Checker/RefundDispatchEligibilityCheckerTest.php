<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Refunding\Checker;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundPluginAvailabilityCheckerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;

final class RefundDispatchEligibilityCheckerTest extends TestCase
{
    use ProphecyTrait;

    private RefundPluginAvailabilityCheckerInterface|ObjectProphecy $refundPluginAvailabilityChecker;

    protected function setUp(): void
    {
        $this->refundPluginAvailabilityChecker = $this->prophesize(RefundPluginAvailabilityCheckerInterface::class);
    }

    public function test_it_returns_true_when_plugin_is_not_available_and_payment_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(false);
        $payment = $this->createPaymentWithFactory('tpay_card');

        $result = $this->createTestSubject()->isEligible($payment->reveal());

        $this->assertTrue($result);
    }

    public function test_it_returns_false_when_plugin_is_available_and_payment_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(true);
        $payment = $this->createPaymentWithFactory('tpay_card');

        $result = $this->createTestSubject()->isEligible($payment->reveal());

        $this->assertFalse($result);
    }

    public function test_it_returns_true_when_plugin_is_available_and_refund_payment_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(true);
        $refundPayment = $this->createRefundPaymentWithFactory('tpay_card');

        $result = $this->createTestSubject()->isEligible($refundPayment->reveal());

        $this->assertTrue($result);
    }

    public function test_it_returns_false_when_plugin_is_not_available_and_refund_payment_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(false);
        $refundPayment = $this->createRefundPaymentWithFactory('tpay_card');

        $result = $this->createTestSubject()->isEligible($refundPayment->reveal());

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_plugin_is_not_available_and_payment_with_non_tpay_factory_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(false);
        $payment = $this->createPaymentWithFactory('stripe');

        $result = $this->createTestSubject()->isEligible($payment->reveal());

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_plugin_is_available_and_payment_with_non_tpay_factory_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(true);
        $payment = $this->createPaymentWithFactory('stripe');

        $result = $this->createTestSubject()->isEligible($payment->reveal());

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_plugin_is_available_and_refund_payment_with_non_tpay_factory_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(true);
        $refundPayment = $this->createRefundPaymentWithFactory('paypal');

        $result = $this->createTestSubject()->isEligible($refundPayment->reveal());

        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_plugin_is_not_available_and_refund_payment_with_non_tpay_factory_is_passed(): void
    {
        $this->refundPluginAvailabilityChecker->isAvailable()->willReturn(false);
        $refundPayment = $this->createRefundPaymentWithFactory('paypal');

        $result = $this->createTestSubject()->isEligible($refundPayment->reveal());

        $this->assertFalse($result);
    }

    private function createPaymentWithFactory(string $factoryName): ObjectProphecy
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn($factoryName);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        return $payment;
    }

    private function createRefundPaymentWithFactory(string $factoryName): ObjectProphecy
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn($factoryName);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $refundPayment = $this->prophesize(RefundPaymentInterface::class);
        $refundPayment->getPaymentMethod()->willReturn($paymentMethod->reveal());

        return $refundPayment;
    }

    private function createTestSubject(): RefundDispatchEligibilityCheckerInterface
    {
        return new RefundDispatchEligibilityChecker(
            $this->refundPluginAvailabilityChecker->reveal(),
        );
    }
}
