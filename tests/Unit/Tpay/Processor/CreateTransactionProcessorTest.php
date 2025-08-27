<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Tpay\Processor;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\PaymentFailedException;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessor;
use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessorInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

final class CreateTransactionProcessorTest extends TestCase
{
    use ProphecyTrait;

    private Payum|ObjectProphecy $payum;

    private CreateTransactionFactoryInterface|ObjectProphecy $createTransactionFactory;

    private GetStatusFactoryInterface|ObjectProphecy $getStatusFactory;

    private TranslatorInterface|ObjectProphecy $translator;

    protected function setUp(): void
    {
        $this->payum = $this->prophesize(Payum::class);
        $this->createTransactionFactory = $this->prophesize(CreateTransactionFactoryInterface::class);
        $this->getStatusFactory = $this->prophesize(GetStatusFactoryInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    public function test_it_processes_a_transaction_creation(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getState()->willReturn(PaymentInterface::STATE_PROCESSING);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction = $this->prophesize(CreateTransaction::class));
        $this->getStatusFactory->createNewWithModel($payment)->willReturn($getStatus = $this->prophesize(GetStatus::class));

        $gateway = $this->prophesize(GatewayInterface::class);
        $gateway->execute($createTransaction, true)->shouldBeCalled();
        $gateway->execute($getStatus, true)->shouldBeCalled();

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->createTestSubject()->process($payment->reveal());
    }

    public function test_it_throws_an_exception_with_an_error_message_if_payment_failed(): void
    {
        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessage('So long and thanks for all the fish');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getState()->willReturn(PaymentInterface::STATE_FAILED);
        $payment->getDetails()->willReturn(['tpay' => ['error_message' => 'So long and thanks for all the fish']]);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction = $this->prophesize(CreateTransaction::class));
        $this->getStatusFactory->createNewWithModel($payment)->willReturn($getStatus = $this->prophesize(GetStatus::class));

        $gateway = $this->prophesize(GatewayInterface::class);
        $gateway->execute($createTransaction, true)->shouldBeCalled();
        $gateway->execute($getStatus, true)->shouldBeCalled();

        $this->payum->getGateway('tpay')->willReturn($gateway);

        $this->createTestSubject()->process($payment->reveal());
    }

    public function test_it_throws_an_exception_with_a_default_message_if_payment_failed(): void
    {
        $this->expectException(PaymentFailedException::class);
        $this->expectExceptionMessage('Payment failed');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getState()->willReturn(PaymentInterface::STATE_FAILED);
        $payment->getDetails()->willReturn([]);

        $this->createTransactionFactory->createNewWithModel($payment)->willReturn($createTransaction = $this->prophesize(CreateTransaction::class));
        $this->getStatusFactory->createNewWithModel($payment)->willReturn($getStatus = $this->prophesize(GetStatus::class));

        $gateway = $this->prophesize(GatewayInterface::class);
        $gateway->execute($createTransaction, true)->shouldBeCalled();
        $gateway->execute($getStatus, true)->shouldBeCalled();

        $this->payum->getGateway('tpay')->willReturn($gateway);
        $this->translator->trans('commerce_weavers_sylius_tpay.shop.payment_failed.error')->willReturn('Payment failed');

        $this->createTestSubject()->process($payment->reveal());
    }

    public function test_it_throws_an_exception_if_gateway_name_cannot_be_extracted(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn(null);

        $this->createTestSubject()->process($payment->reveal());
    }

    private function createTestSubject(): CreateTransactionProcessorInterface
    {
        return new CreateTransactionProcessor(
            $this->payum->reveal(),
            $this->createTransactionFactory->reveal(),
            $this->getStatusFactory->reveal(),
            $this->translator->reveal(),
        );
    }
}
