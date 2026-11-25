<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\PartialRefundAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\RefundCannotBeMadeException;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class PartialRefundActionTest extends TestCase
{
    use ProphecyTrait;

    private GatewayInterface|ObjectProphecy $gateway;

    private TpayApi|ObjectProphecy $tpayApi;

    private RefundPaymentInterface|ObjectProphecy $refundPayment;

    private Refund|ObjectProphecy $refundRequest;

    protected function setUp(): void
    {
        $this->gateway = $this->prophesize(GatewayInterface::class);
        $this->tpayApi = $this->prophesize(TpayApi::class);
        $this->refundPayment = $this->prophesize(RefundPaymentInterface::class);
        $this->refundRequest = $this->prophesize(Refund::class);

        $this->refundRequest->getModel()->willReturn($this->refundPayment);
    }

    public function test_it_performs_a_full_refund_if_payment_amount_is_equal_to_refund_amount(): void
    {
        $paymentDetails = [
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_!d',
            ],
        ];
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn($paymentDetails);
        $payment->getAmount()->willReturn(100);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment);

        $this->refundPayment->getAmount()->willReturn(100);
        $this->refundPayment->getOrder()->willReturn($order);

        $this->gateway->execute(Argument::that(function (Refund $refund) use ($payment): bool {
            return $refund->getModel() === $payment->reveal();
        }))->shouldBeCalled();

        $this->tpayApi->transactions()->shouldNotBeCalled();

        $this->createTestSubject()->execute($this->refundRequest->reveal());
    }

    public function test_it_performs_a_partial_refund(): void
    {
        $paymentDetails = [
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_!d',
            ],
        ];
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn($paymentDetails);
        $payment->getAmount()->willReturn(100);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment);

        $this->refundPayment->getAmount()->willReturn(90);
        $this->refundPayment->getOrder()->willReturn($order);

        $this->gateway->execute(Argument::any())->shouldNotBeCalled();

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createRefundByTransactionId(['amount' => 0.90], 'tr4ns4ct!0n_!d')
            ->willReturn([
                'result' => 'success',
            ])
            ->shouldBeCalled()
        ;

        $this->tpayApi->transactions()->willReturn($transactions);

        $this->createTestSubject()->execute($this->refundRequest->reveal());
    }

    public function test_it_throws_an_exception_if_payment_transaction_id_is_missing(): void
    {
        $this->expectException(RefundCannotBeMadeException::class);
        $this->expectExceptionMessage('Tpay transaction id cannot be found.');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getAmount()->willReturn(100);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment);

        $this->refundPayment->getAmount()->willReturn(90);
        $this->refundPayment->getOrder()->willReturn($order);

        $this->gateway->execute(Argument::any())->shouldNotBeCalled();
        $this->tpayApi->transactions()->shouldNotBeCalled();

        $this->createTestSubject()->execute($this->refundRequest->reveal());    }

    public function test_it_throws_an_exception_when_tpay_rejects_the_partial_refund(): void
    {
        $this->expectException(RefundCannotBeMadeException::class);
        $this->expectExceptionMessage('No sufficient funds or the refund limit has been reached');

        $paymentDetails = [
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_!d',
            ],
        ];
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn($paymentDetails);
        $payment->getAmount()->willReturn(100);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn($payment);

        $this->refundPayment->getAmount()->willReturn(90);
        $this->refundPayment->getOrder()->willReturn($order);

        $this->gateway->execute(Argument::any())->shouldNotBeCalled();

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createRefundByTransactionId(['amount' => 0.90], 'tr4ns4ct!0n_!d')
            ->willReturn([
                'result' => 'failed',
                'requestId' => 'b5c1e1ba6f5e0d8a4c2f',
                'errors' => [
                    [
                        'errorCode' => 'no_sufficient_funds_or_refund_limit_reached',
                        'errorMessage' => 'No sufficient funds or the refund limit has been reached',
                        'fieldName' => '',
                        'devMessage' => '',
                        'docUrl' => '',
                    ],
                ],
            ])
        ;

        $this->tpayApi->transactions()->willReturn($transactions);

        $this->createTestSubject()->execute($this->refundRequest->reveal());
    }

    private function createTestSubject(): PartialRefundAction
    {
        $action = new PartialRefundAction();

        $action->setApi($this->tpayApi->reveal());
        $action->setGateway($this->gateway->reveal());

        return $action;
    }
}
