<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\CardPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Action\CreateCardTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Request\Api\PayWithCard;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\GatewayInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\PaymentDetailsHelperTrait;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Tpay\OpenApi\Utilities\TpayException;

final class CreateCardTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private TpayApi|ObjectProphecy $api;

    private CreateCardPaymentPayloadFactoryInterface|ObjectProphecy $createCardPaymentPayloadFactory;

    private GenericTokenFactoryInterface|ObjectProphecy $tokenFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    private GatewayInterface|ObjectProphecy $gateway;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createCardPaymentPayloadFactory = $this->prophesize(CreateCardPaymentPayloadFactoryInterface::class);
        $this->tokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
        $this->gateway = $this->prophesize(GatewayInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['card' => 'hashed_card']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertTrue($isSupported);
    }

    public function test_it_does_not_support_non_create_transaction_requests(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);

        $request = $this->prophesize(Capture::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_support_requests_with_non_payment_model(): void
    {
        $nonPaymentModel = new \stdClass();

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($nonPaymentModel);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_support_requests_with_payment_model_not_containing_tpay_card(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['blik' => '123456']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_creates_a_payment_and_requests_paying_it_with_a_provided_card(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'result' => 'success',
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', payment_url: 'https://tpay.org/pay'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createCardPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
            ->shouldBeCalled()
        ;

        $this->gateway->execute(Argument::that(function (PayWithCard $request) use ($payment): bool {
            return $request->getModel() === $payment->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_marks_payment_as_failed_if_tpay_throws_an_exception(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willThrow(new TpayException('Something went wrong'));

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(status: 'failed'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createCardPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->gateway->execute(Argument::that(function (PayWithCard $request) use ($payment): bool {
            return $request->getModel() === $payment->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_tries_to_determine_a_gateway_name_by_model_once_token_is_not_present(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('tpay');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'result' => 'success',
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', payment_url: 'https://tpay.org/pay'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createCardPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->gateway->execute(Argument::that(function (PayWithCard $request) use ($payment): bool {
            return $request->getModel() === $payment->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_an_exception_when_a_gateway_name_cannot_be_determined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine gateway name for a given payment');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getMethod()->willReturn(null);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): CreateCardTransactionAction
    {
        $action = new CreateCardTransactionAction(
            $this->createCardPaymentPayloadFactory->reveal(),
            $this->notifyTokenFactory->reveal(),
        );

        $action->setApi($this->api->reveal());
        $action->setGenericTokenFactory($this->tokenFactory->reveal());
        $action->setGateway($this->gateway->reveal());

        return $action;
    }
}
