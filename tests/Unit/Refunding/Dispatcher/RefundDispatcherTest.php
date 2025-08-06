<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Refunding\Dispatcher;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcher;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcherInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;

final class RefundDispatcherTest extends TestCase
{
    use ProphecyTrait;

    private Payum|ObjectProphecy $payum;

    private RefundDispatchEligibilityCheckerInterface|ObjectProphecy $eligibilityChecker;

    protected function setUp(): void
    {
        $this->payum = $this->prophesize(Payum::class);
        $this->eligibilityChecker = $this->prophesize(RefundDispatchEligibilityCheckerInterface::class);
    }

    public function test_it_executes_a_refund_request_with_payment_if_eligible(): void
    {
        $payment = $this->createPayment();
        $this->eligibilityChecker->isEligible($payment->reveal())->willReturn(true);

        $this->payum->getGateway('tpay')->willReturn($gateway = $this->prophesize(GatewayInterface::class));

        $gateway->execute(Argument::that(function (Refund $refund) use ($payment): bool {
            return $refund->getModel() === $payment->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->dispatch($payment->reveal());
    }

    public function test_it_does_nothing_if_payment_is_not_eligible(): void
    {
        $payment = $this->createPayment();
        $this->eligibilityChecker->isEligible($payment->reveal())->willReturn(false);

        $this->payum->getGateway('tpay')->willReturn($gateway = $this->prophesize(GatewayInterface::class));

        $gateway->execute(Argument::any())->shouldNotBeCalled();

        $this->createTestSubject()->dispatch($payment->reveal());
    }

    public function test_it_executes_a_refund_request_with_refund_payment_if_eligible(): void
    {
        $refundPayment = $this->createRefundPayment();
        $this->eligibilityChecker->isEligible($refundPayment->reveal())->willReturn(true);

        $this->payum->getGateway('tpay')->willReturn($gateway = $this->prophesize(GatewayInterface::class));

        $gateway->execute(Argument::that(function (Refund $refund) use ($refundPayment): bool {
            return $refund->getModel() === $refundPayment->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->dispatch($refundPayment->reveal());
    }

    public function test_it_does_nothing_if_refund_payment_is_not_eligible(): void
    {
        $refundPayment = $this->createRefundPayment();
        $this->eligibilityChecker->isEligible($refundPayment->reveal())->willReturn(false);

        $this->payum->getGateway('tpay')->willReturn($gateway = $this->prophesize(GatewayInterface::class));

        $gateway->execute(Argument::any())->shouldNotBeCalled();

        $this->createTestSubject()->dispatch($refundPayment->reveal());
    }

    private function createPayment(): PaymentInterface|ObjectProphecy
    {
        $gatewayName = 'tpay';

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn($gatewayName);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);

        return $payment;
    }

    private function createRefundPayment(): RefundPaymentInterface|ObjectProphecy
    {
        $gatewayName = 'tpay';

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn($gatewayName);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $refundPayment = $this->prophesize(RefundPaymentInterface::class);
        $refundPayment->getPaymentMethod()->willReturn($paymentMethod);

        return $refundPayment;
    }

    private function createTestSubject(): RefundDispatcherInterface
    {
        return new RefundDispatcher(
            $this->payum->reveal(),
            $this->eligibilityChecker->reveal(),
        );
    }
}
