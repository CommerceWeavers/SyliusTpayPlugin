<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByRedirect;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByRedirectHandler;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\InvalidArgumentException;

final class PayByRedirectHandlerTest extends TestCase
{
    use ProphecyTrait;

    private PaymentRepositoryInterface|ObjectProphecy $paymentRepository;

    private Payum|ObjectProphecy $payum;

    private CreateTransactionFactoryInterface|ObjectProphecy $createTransactionFactory;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->prophesize(PaymentRepositoryInterface::class);
        $this->payum = $this->prophesize(Payum::class);
        $this->createTransactionFactory = $this->prophesize(CreateTransactionFactoryInterface::class);
    }

    public function test_it_throw_an_exception_if_a_payment_cannot_be_found(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Payment with id "1" cannot be found.');

        $this->paymentRepository->find(1)->willReturn(null);

        $this->createTestSubject()->__invoke(new PayByRedirect(1));
    }

    public function test_it_throws_an_exception_if_a_gateway_name_cannot_be_determined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway name cannot be determined.');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(Argument::any());
        $payment->getMethod()->willReturn(null);

        $this->paymentRepository->find(1)->willReturn($payment);

        $this->createTestSubject()->__invoke(new PayByRedirect(1));
    }

    public function test_it_throws_an_exception_if_payment_details_does_not_have_a_set_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment status is required to create a result.');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['payment_url' => 'https://cw.org/pay']]);

        $this->paymentRepository->find(1)->willReturn($payment);

        $createTransaction = $this->prophesize(CreateTransaction::class);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);

        $gateway = $this->prophesize(GatewayInterface::class);

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->createTestSubject()->__invoke(new PayByRedirect(1));
    }

    public function test_it_throws_an_exception_if_payment_details_does_not_have_a_set_payment_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment URL is required to create a result.');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['status' => 'pending']]);

        $this->paymentRepository->find(1)->willReturn($payment);

        $createTransaction = $this->prophesize(CreateTransaction::class);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);

        $gateway = $this->prophesize(GatewayInterface::class);

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->createTestSubject()->__invoke(new PayByRedirect(1));
    }

    public function test_it_creates_a_redirect_based_transaction(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['status' => 'pending', 'payment_url' => 'https://cw.org/pay']]);

        $this->paymentRepository->find(1)->willReturn($payment);

        $createTransaction = $this->prophesize(CreateTransaction::class);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);

        $gateway = $this->prophesize(GatewayInterface::class);
        $gateway->execute($createTransaction, catchReply: true)->shouldBeCalled();

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $result = $this->createTestSubject()->__invoke(new PayByRedirect(1));

        self::assertSame('pending', $result->status);
        self::assertSame('https://cw.org/pay', $result->transactionPaymentUrl);
    }

    private function createTestSubject(): PayByRedirectHandler
    {
        return new PayByRedirectHandler(
            $this->paymentRepository->reveal(),
            $this->payum->reveal(),
            $this->createTransactionFactory->reveal(),
        );
    }
}
