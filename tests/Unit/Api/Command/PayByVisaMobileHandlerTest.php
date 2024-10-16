<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Command;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByVisaMobile;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByVisaMobileHandler;
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

class PayByVisaMobileHandlerTest extends TestCase
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

        $this->createTestSubject()->__invoke(new PayByVisaMobile(1, visaMobilePhoneNumber: '44123456789'));
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

        $this->createTestSubject()->__invoke(new PayByVisaMobile(1, visaMobilePhoneNumber: '44123456789'));
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
        $payment->getDetails()->willReturn(['tpay' => ['status' => null]]);
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => null,
            ],
        ])->shouldBeCalled();

        $this->paymentRepository->find(1)->willReturn($payment);

        $createTransaction = $this->prophesize(CreateTransaction::class);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);

        $gateway = $this->prophesize(GatewayInterface::class);

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->createTestSubject()->__invoke(new PayByVisaMobile(1, visaMobilePhoneNumber: '44123456789'));
    }

    public function test_it_creates_a_visa_mobile_based_transaction(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['status' => 'pending', 'payment_url' => 'https://cw.org/pay', 'visa_mobile_phone_number' => '44123456789']]);
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => 'pending',
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => 'https://cw.org/pay',
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => '44123456789',
            ],
        ])->shouldBeCalled();

        $this->paymentRepository->find(1)->willReturn($payment);

        $createTransaction = $this->prophesize(CreateTransaction::class);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction);

        $gateway = $this->prophesize(GatewayInterface::class);
        $gateway->execute($createTransaction, catchReply: true)->shouldBeCalled();

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $result = $this->createTestSubject()->__invoke(new PayByVisaMobile(1, visaMobilePhoneNumber: '44123456789'));

        self::assertSame('pending', $result->status);
        self::assertSame('https://cw.org/pay', $result->transactionPaymentUrl);
    }

    private function createTestSubject(): PayByVisaMobileHandler
    {
        return new PayByVisaMobileHandler(
            $this->paymentRepository->reveal(),
            $this->payum->reveal(),
            $this->createTransactionFactory->reveal(),
        );
    }
}
