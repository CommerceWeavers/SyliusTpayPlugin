<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlik;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayHandler;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayResult;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class PayHandlerTest extends TestCase
{
    use ProphecyTrait;

    private OrderRepositoryInterface|ObjectProphecy $orderRepository;

    private NextCommandFactoryInterface|ObjectProphecy $nextCommandFactory;

    private MessageBusInterface|ObjectProphecy $messageBus;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->nextCommandFactory = $this->prophesize(NextCommandFactoryInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
    }

    public function test_it_throws_an_exception_if_an_order_cannot_be_found(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->orderRepository->findOneByTokenValue('token')->willReturn(null);

        $this->createTestSubject()->__invoke($this->createCommand());
    }

    public function test_it_throws_an_exception_if_a_payment_cannot_be_found(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $this->orderRepository->findOneByTokenValue('token')->willReturn($order);

        $this->createTestSubject()->__invoke($this->createCommand());
    }

    public function test_it_executes_pay_by_blik_command_if_a_blik_token_is_passed(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment = $this->prophesize(PaymentInterface::class));

        $this->orderRepository->findOneByTokenValue('token')->willReturn($order);

        $payment->getId()->willReturn(1);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => null,
                'apple_pay_token' => null,
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'tpay_channel_id' => null,
                'payment_url' => null,
                'success_url' => 'https://cw.nonexisting/success',
                'failure_url' => 'https://cw.nonexisting/failure',
                'visa_mobile' => false,
            ],
        ])->shouldBeCalled();

        $this->nextCommandFactory->create(Argument::type(Pay::class), $payment)->willReturn(new PayByBlik(1, '777123'));

        $payResult = new PayResult('success');
        $payResultEnvelope = new Envelope(new \stdClass(), [new HandledStamp($payResult, 'dummy_handler')]);

        $this->messageBus
            ->dispatch(Argument::that(function (PayByBlik $pay): bool {
                return $pay->blikToken === '777123' && $pay->paymentId === 1;
            }))
            ->shouldBeCalled()
            ->willReturn($payResultEnvelope)
        ;

        $result = $this->createTestSubject()->__invoke($this->createCommand(blikToken: '777123'));

        $this->assertSame($payResult, $result);
    }

    private function createCommand(?string $token = null, ?string $blikToken = null): Pay
    {
        return new Pay(
            $token ?? 'token',
            'https://cw.nonexisting/success',
            'https://cw.nonexisting/failure',
            blikToken: $blikToken,
        );
    }

    private function createTestSubject(): PayHandler
    {
        return new PayHandler(
            $this->orderRepository->reveal(),
            $this->nextCommandFactory->reveal(),
            $this->messageBus->reveal()
        );
    }
}
