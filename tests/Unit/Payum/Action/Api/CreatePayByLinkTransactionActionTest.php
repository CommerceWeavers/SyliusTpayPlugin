<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreatePayByLinkTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class CreatePayByLinkTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $api;

    private CreatePayByLinkPayloadFactoryInterface|ObjectProphecy $createPayByLinkPayloadFactory;

    private GenericTokenFactoryInterface|ObjectProphecy $tokenFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createPayByLinkPayloadFactory = $this->prophesize(CreatePayByLinkPayloadFactoryInterface::class);
        $this->tokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['pay_by_link_channel_id' => 1]]);

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

    public function test_it_does_not_support_requests_with_payment_model_not_containing_tpay_pbl_channel_id(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn(['tpay' => ['blik' => '123456']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_creates_a_payment_and_redirects_to_a_payment_page(): void
    {
        $this->expectException(HttpRedirect::class);

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
            'status' => 'pending',
            'transactionId' => 'tr4ns4ct!0n_id',
            'transactionPaymentUrl' => 'https://tpay.org/pay',
        ]);

        $this->api->transactions()->willReturn($transactions);

        $payment->setDetails([
            'tpay' => [
                'transaction_id' => 'tr4ns4ct!0n_id',
                'result' => null,
                'status' => 'pending',
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => 'https://tpay.org/pay',
                'success_url' => null,
                'failure_url' => null,
            ],
        ])->shouldBeCalled();

        $this->notifyTokenFactory->create($payment, 'tpay', 'pl_PL')->willReturn($notifyToken);

        $this->createPayByLinkPayloadFactory
            ->createFrom($payment, 'https://cw.org/notify', 'pl_PL')
            ->willReturn(['factored' => 'payload'])
        ;

        $this->createTestSubject()->execute($request->reveal());
    }

    private function createTestSubject(): CreatePayByLinkTransactionAction
    {
        $action = new CreatePayByLinkTransactionAction(
            $this->createPayByLinkPayloadFactory->reveal(),
            $this->notifyTokenFactory->reveal(),
        );

        $action->setApi($this->api->reveal());
        $action->setGenericTokenFactory($this->tokenFactory->reveal());

        return $action;
    }
}
