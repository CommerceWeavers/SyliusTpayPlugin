<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Command;

use CommerceWeavers\SyliusTpayPlugin\Command\CancelLastPayment;
use CommerceWeavers\SyliusTpayPlugin\Command\CancelLastPaymentHandler;
use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCancellerInterface;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CancelLastPaymentHandlerTest extends TestCase
{
    use ProphecyTrait;

    private OrderRepositoryInterface|ObjectProphecy $orderRepository;

    private PaymentCancellationPossibilityCheckerInterface|ObjectProphecy $paymentCancellationPossibilityChecker;

    private PaymentCancellerInterface|ObjectProphecy $paymentCanceller;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->paymentCancellationPossibilityChecker = $this->prophesize(PaymentCancellationPossibilityCheckerInterface::class);
        $this->paymentCanceller = $this->prophesize(PaymentCancellerInterface::class);
    }

    public function test_command_returns_orderToken_as_order_token_property_name(): void
    {
        $command = new CancelLastPayment('token');

        $this->assertSame(
            'orderToken',
            $command::getOrderTokenPropertyName()
        );

    }

    public function test_it_cancels_last_payment(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order->reveal());

        $this->paymentCancellationPossibilityChecker->canBeCancelled($payment)->willReturn(true);
        $this->paymentCanceller->cancel($payment)->shouldBeCalled();

        $this->createTestSubject()->__invoke(new CancelLastPayment('t0k3n'));
    }

    public function test_it_throws_an_exception_if_an_order_with_a_given_token_cannot_be_found(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Order with token "t0k3n" not found.');

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn(null);

        $this->createTestSubject()->__invoke(new CancelLastPayment('t0k3n'));
    }

    public function test_it_throws_an_exception_if_a_given_order_has_no_last_payment(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The last payment for order with token "t0k3n" not found.');

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn(null);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order->reveal());

        $this->createTestSubject()->__invoke(new CancelLastPayment('t0k3n'));
    }

    public function test_it_throws_an_exception_if_last_payment_cannot_be_cancelled(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order->reveal());

        $this->paymentCancellationPossibilityChecker->canBeCancelled($payment)->willReturn(false);
        $this->paymentCanceller->cancel($payment)->shouldNotBeCalled();

        $this->expectException(PaymentCannotBeCancelledException::class);

        $this->createTestSubject()->__invoke(new CancelLastPayment('t0k3n'));
    }

    private function createTestSubject(): CancelLastPaymentHandler
    {
        return new CancelLastPaymentHandler(
            $this->orderRepository->reveal(),
            $this->paymentCancellationPossibilityChecker->reveal(),
            $this->paymentCanceller->reveal(),
        );
    }
}
