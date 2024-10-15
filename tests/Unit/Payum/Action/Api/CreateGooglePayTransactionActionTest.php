<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateGooglePayTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateGooglePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class CreateGooglePayTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $api;

    private CreateGooglePayPaymentPayloadFactoryInterface|ObjectProphecy $createGooglePayPaymentPayloadFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createGooglePayPaymentPayloadFactory = $this->prophesize(CreateGooglePayPaymentPayloadFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $request->getModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['tpay' => ['google_pay_token' => 'yolo!']]);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertTrue($isSupported);
    }

    public function test_it_does_not_support_requests_with_non_payment_model(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = new \stdClass();
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

    public function test_it_does_not_support_requests_with_payment_model_not_containing_tpay_google_pay_token(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $request->getModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['foo' => ['bar' => 'baz']]);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_creates_a_payment_and_requests_paying_it_with_a_provided_google_pay_token(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $notifyToken = $this->prophesize(TokenInterface::class);
        $transactions = $this->prophesize(TransactionsApi::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $token->getGatewayName()->willReturn('tpay');
        $order->getLocaleCode()->willReturn('pl_PL');
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');
        $this->createGooglePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'transactionId' => 'tr4ns4ct!0n_id',
            'status' => 'correct',
        ]);

        $this->createTestSubject()->execute($request->reveal());

        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => 'correct',
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile' => false,
            ],
        ])->shouldBeCalled();
    }

    public function test_it_redirects_payment_if_3d_secure_authentication_required(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $notifyToken = $this->prophesize(TokenInterface::class);
        $transactions = $this->prophesize(TransactionsApi::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $token->getGatewayName()->willReturn('tpay');
        $order->getLocaleCode()->willReturn('pl_PL');
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');
        $this->createGooglePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'transactionId' => 'tr4ns4ct!0n_id',
            'status' => 'pending',
            'transactionPaymentUrl' => 'https://tpay.org/pay'
        ]);

        $this->expectException(HttpRedirect::class);
        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => 'pending',
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => 'https://tpay.org/pay',
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile' => false,
            ],
        ])->shouldBeCalled();

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_exception_if_3d_secure_authentication_required_but_transaction_payment_url_is_missing(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $notifyToken = $this->prophesize(TokenInterface::class);
        $transactions = $this->prophesize(TransactionsApi::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $token->getGatewayName()->willReturn('tpay');
        $order->getLocaleCode()->willReturn('pl_PL');
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');
        $this->createGooglePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'transactionId' => 'tr4ns4ct!0n_id',
            'status' => 'pending',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot perform 3DS redirect. Missing transactionPaymentUrl in the response.');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_tries_to_determine_a_gateway_name_by_model_once_token_is_not_present(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $notifyToken = $this->prophesize(TokenInterface::class);
        $transactions = $this->prophesize(TransactionsApi::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('tpay');
        $order->getLocaleCode()->willReturn('pl_PL');
        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);
        $this->api->transactions()->willReturn($transactions);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');
        $this->createGooglePayPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'transactionId' => 'tr4ns4ct!0n_id',
            'status' => 'correct',
        ]);

        $this->createTestSubject()->execute($request->reveal());

        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => 'correct',
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile' => false,
            ],
        ])->shouldBeCalled();
    }

    public function test_it_throws_an_exception_when_a_gateway_name_cannot_be_determined(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine gateway name for a given payment');

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_throws_an_exception_when_a_locale_code_cannot_be_determined(): void
    {
        $request = $this->prophesize(CreateTransaction::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);
        $token->getGatewayName()->willReturn('tpay');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine locale code for a given payment');

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): CreateGooglePayTransactionAction
    {
        $action = new CreateGooglePayTransactionAction(
            $this->createGooglePayPaymentPayloadFactory->reveal(),
            $this->notifyTokenFactory->reveal(),
        );

        $action->setApi($this->api->reveal());

        return $action;
    }
}
