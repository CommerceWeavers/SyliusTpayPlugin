<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByGooglePay;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByGooglePayHandler;
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

final class PayByGooglePayHandlerTest extends TestCase
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
        $this->paymentRepository->find(1)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Payment with id "1" cannot be found.');

        $this->createTestSubject()->__invoke(new PayByGooglePay(1, 't00k33n'));
    }

    public function test_it_throws_an_exception_if_a_gateway_name_cannot_be_determined(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getMethod()->willReturn(null);
        $this->paymentRepository->find(1)->willReturn($payment);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway name cannot be determined.');
        $payment->setDetails(Argument::any())->shouldBeCalled();

        $this->createTestSubject()->__invoke(new PayByGooglePay(1, 't00k33n'));
    }

    public function test_it_throws_an_exception_if_a_payment_status_is_null(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $createTransaction = $this->prophesize(CreateTransaction::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn([], ['tpay' => []]);
        $this->paymentRepository->find(1)->willReturn($payment);
        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);
        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment status is required to create a result.');
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => null,
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => 't00k33n',
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => null,
                'visa_mobile_phone_number' => null,
            ],
        ])->shouldBeCalled();
        $gateway->execute($createTransaction, catchReply: true)->shouldBeCalled();

        $this->createTestSubject()->__invoke(new PayByGooglePay(1, 't00k33n'));
    }

    public function test_it_creates_a_google_pay_based_transaction(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $createTransaction = $this->prophesize(CreateTransaction::class);
        $gateway = $this->prophesize(GatewayInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn([], ['tpay' => ['status' => 'pending']]);
        $this->paymentRepository->find(1)->willReturn($payment);
        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);
        $this->payum->getGateway('tpay')->willReturn($gateway);

        $result = $this->createTestSubject()->__invoke(new PayByGooglePay(1, 't00k33n'));

        self::assertSame('pending', $result->status);
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => null,
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => 't00k33n',
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => null,
            ],
        ])->shouldBeCalled();
        $gateway->execute($createTransaction, catchReply: true)->shouldBeCalled();
    }

    private function createTestSubject(): PayByGooglePayHandler
    {
        return new PayByGooglePayHandler(
            $this->paymentRepository->reveal(),
            $this->payum->reveal(),
            $this->createTransactionFactory->reveal(),
        );
    }
}
