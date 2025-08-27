<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\ApplePayPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\ApplePayPayment\Payum\Action\CreateApplePayTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateApplePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\PaymentDetailsHelperTrait;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Tpay\OpenApi\Utilities\TpayException;

final class CreateApplePayTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private TpayApi|ObjectProphecy $api;

    private CreateApplePayPaymentPayloadFactoryInterface|ObjectProphecy $createApplePayPaymentPayloadFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createApplePayPaymentPayloadFactory = $this->prophesize(CreateApplePayPaymentPayloadFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['apple_pay_token' => 'ewogInRwYXkiIDogIkhlbGxvIFdvcmxkIgp9']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertTrue($isSupported);
    }

    public function test_it_does_not_support_requests_with_non_payment_model(): void
    {
        $payment = new \stdClass();

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_support_non_create_transaction_requests(): void
    {
        $request = new \stdClass();

        $isSupported = $this->createTestSubject()->supports($request);

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_support_requests_with_payment_model_not_containing_tpay_apple_pay_token(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['foo' => ['bar' => 'baz']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_creates_a_payment_and_requests_paying_it_with_a_provided_apple_pay_token(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'correct'),
        )->shouldBeCalled();

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
            'status' => 'correct',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $this->createApplePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_redirects_payment_if_3d_secure_authentication_required(): void
    {
        $this->expectException(HttpRedirect::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'pending', payment_url: 'https://tpay.org/pay'),
        )->shouldBeCalled();

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
            'status' => 'pending',
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay'
        ]);

        $this->createApplePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_marks_payment_as_failed_if_tpay_throws_an_exception(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(status: 'failed'),
        )->shouldBeCalled();

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willThrow(new TpayException('Some message'));

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $this->createApplePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(Argument::any())->willThrow(new TpayException('Some message'));
    }

    public function test_it_throws_exception_if_3d_secure_authentication_required_but_transaction_payment_url_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform 3DS redirect. Missing transactionPaymentUrl in the response.');

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'pending'),
        )->shouldBeCalled();

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
            'status' => 'pending',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->createApplePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);

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
        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'correct'),
        )->shouldBeCalled();

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'result' => 'success',
            'status' => 'correct',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $this->createApplePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_an_exception_when_a_gateway_name_cannot_be_determined(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getMethod()->willReturn(null);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine gateway name for a given payment');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_an_exception_when_a_locale_code_cannot_be_determined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine locale code for a given payment');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);
        $payment->getOrder()->willReturn(null);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): CreateApplePayTransactionAction
    {
        $action = new CreateApplePayTransactionAction(
            $this->createApplePayPaymentPayloadFactory->reveal(),
            $this->notifyTokenFactory->reveal(),
        );

        $action->setApi($this->api->reveal());

        return $action;
    }
}
