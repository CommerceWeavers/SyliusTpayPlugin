<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateCardTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\PayWithCard;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
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
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class CreateCardTransactionActionTest extends TestCase
{
    use ProphecyTrait;

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
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => null,
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

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createCardPaymentPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->gateway->execute(Argument::that(function (PayWithCard $request) use ($token): bool {
            return $request->getToken() === $token->reveal();
        }))->shouldBeCalled();

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_tries_to_determine_a_gateway_name_by_model_once_token_is_not_present(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay');

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
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => null,
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

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($this->prophesize(PaymentInterface::class)->reveal());
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
