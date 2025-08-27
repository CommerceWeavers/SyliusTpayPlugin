<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\BlikPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Action\CreateBlikLevelZeroTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Exception\BlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Repository\BlikAliasRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\GatewayInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Payum\Core\Request\Capture;
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

final class CreateBlikLevelZeroTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private TpayApi|ObjectProphecy $api;

    private CreateBlikLevelZeroPaymentPayloadFactoryInterface|ObjectProphecy $createBlikLevelZeroPaymentPayloadFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    private BlikAliasRepositoryInterface|ObjectProphecy $blikAliasRepository;

    private GatewayInterface|ObjectProphecy $gateway;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createBlikLevelZeroPaymentPayloadFactory = $this->prophesize(CreateBlikLevelZeroPaymentPayloadFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
        $this->blikAliasRepository = $this->prophesize(BlikAliasRepositoryInterface::class);
        $this->gateway = $this->prophesize(GatewayInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['blik_token' => '123456']]);

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

    public function test_it_does_not_support_requests_with_payment_model_not_containing_tpay_blik(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['card' => 'some_crazy_card_hash']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_creates_a_payment_and_requests_paying_it(): void
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
            'status' => 'correct',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->blikAliasRepository->findOneByValue(Argument::any())->shouldNotBeCalled();

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'correct'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

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
        $transactions->createTransaction(['factored' => 'payload'])->willThrow(new TpayException('some_error'));

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(status: 'failed'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_creates_a_payment_and_requests_paying_it_using_blik_alias(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn(['tpay' => ['blik_alias_value' => 'i_AM_a_BLIK_alias_VALUE']]);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);
        $request->getToken()->willReturn($token);

        $notifyToken = $this->prophesize(TokenInterface::class);
        $notifyToken->getTargetUrl()->willReturn('https://cw.org/notify');

        $blikAlias = $this->prophesize(BlikAliasInterface::class);

        $this->blikAliasRepository->findOneByValue('i_AM_a_BLIK_alias_VALUE')->willReturn($blikAlias);

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createTransaction(['factored' => 'payload'])->willReturn([
            'result' => 'success',
            'status' => 'correct',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(
                transaction_id: 'tr4ns4ct!0n_id',
                status: 'correct',
                blik_alias_value: 'i_AM_a_BLIK_alias_VALUE',
            ),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, $blikAlias, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

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
            'status' => 'correct',
            'transactionId' => 'tr4ns4ct!0n_id',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails(
            $this->getExpectedDetails(transaction_id: 'tr4ns4ct!0n_id', status: 'correct'),
        )->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

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

    public function test_it_handles_payment_error(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(
                transaction_id: 'tr4ns4ct!0n_id',
                status: 'failed',
                error_message: 'I do not know what happened here but it does not work LOL!',
            ),
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
            'payments' => [
                'errors' => [
                    [
                        'errorCode' => 'yolo',
                        'errorMessage' => 'I do not know what happened here but it does not work LOL!',
                    ],
                ],
            ],
        ]);

        $this->api->transactions()->willReturn($transactions);

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_handles_errors_and_throws_exception_if_unexpected_error_occurred(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexpected error.');

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(),
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
            'payments' => [
                'errors' => [
                    [
                        'errorCode' => 'yolo',
                        'errorMessage' => 'I do not know what happened here but it does not work LOL!',
                    ],
                ],
            ],
        ]);

        $this->api->transactions()->willReturn($transactions);

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    public function test_it_handles_errors_and_throws_blik_alias_ambiguous_value_exception_if_there_are_alternatives_in_the_response(): void
    {
        $this->expectExceptionObject(BlikAliasAmbiguousValueException::create([
            [
                'applicationName' => 'Acme Bank',
                'applicationCode' => '1ec8f352-463c-6334-be44-9fede70e64b8',
            ],
        ]));

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('pl_PL');

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->setDetails(
            $this->getExpectedDetails(),
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
            'payments' => [
                'errors' => [
                    [
                        'errorCode' => 'payment_failed',
                        'errorMessage' => 'aliases: Too many aliases found for aliasValue: ambiguous_value',
                    ],
                ],
                'alternatives' => [
                    [
                        'applicationName' => 'Acme Bank',
                        'applicationCode' => '1ec8f352-463c-6334-be44-9fede70e64b8',
                    ],
                ]
            ],
        ]);

        $this->api->transactions()->willReturn($transactions);

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createBlikLevelZeroPaymentPayloadFactory
            ->createFrom($payment, null, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): CreateBlikLevelZeroTransactionAction
    {
        $action = new CreateBlikLevelZeroTransactionAction(
            $this->createBlikLevelZeroPaymentPayloadFactory->reveal(),
            $this->notifyTokenFactory->reveal(),
            $this->blikAliasRepository->reveal(),
        );

        $action->setApi($this->api->reveal());

        return $action;
    }
}
