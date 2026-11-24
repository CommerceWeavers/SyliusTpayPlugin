<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\CardPayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Action\PayWithCardAction;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Mapper\PayWithCardActionPayloadMapperInterface;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Payum\Request\Api\PayWithCard;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentDetails;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\PaymentDetailsHelperTrait;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;
use Tpay\OpenApi\Utilities\TpayException;

final class PayWithCardActionTest extends TestCase
{
    use ProphecyTrait;

    use PaymentDetailsHelperTrait;

    private TpayApi|ObjectProphecy $api;
    private PayWithCardActionPayloadMapperInterface|ObjectProphecy $mapper;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->mapper = $this->prophesize(PayWithCardActionPayloadMapperInterface::class);
    }

    public function test_it_supports_pay_with_card_request_with_a_payment_model(): void
    {
        $request = $this->prophesize(PayWithCard::class);
        $request->getModel()->willReturn($this->prophesize(PaymentInterface::class)->reveal());

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertTrue($isSupported);
    }

    public function test_it_does_not_supports_other_request(): void
    {
        $request = $this->prophesize(\stdClass::class);
        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_supports_pay_with_card_request_with_non_payment_model(): void
    {
        $request = $this->prophesize(PayWithCard::class);
        $request->getModel()->willReturn($this->prophesize(\stdClass::class)->reveal());

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
    }

    public function test_it_redirects_a_customer_to_3ds_verification_once_a_transaction_status_is_pending(): void
    {
        $this->expectException(HttpRedirect::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $request = $this->prophesize(PayWithCard::class);
        $details = [
            'tpay' => [
                'card' => 'test-card',
                'transaction_id' => 'abcd',
            ],
        ];

        $response = [
            'result' => 'success',
            'status' => 'pending',
            'transactionPaymentUrl' => 'http://example.com',
        ];

        $paymentModel = $this->prophesize(PaymentInterface::class);
        $paymentModel->getOrder()->willReturn($order->reveal());
        $paymentModel->getDetails()->willReturn($details);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request->getModel()->willReturn($paymentModel->reveal());
        $request->getToken()->willReturn($token->reveal());

        $this->mapper->getPayload(Argument::type(PaymentDetails::class), $paymentModel->reveal())->willReturn(['GENERATED' => 'PAYLOAD']);

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createPaymentByTransactionId(['GENERATED' => 'PAYLOAD'], $details['tpay']['transaction_id'])->willReturn($response);

        $this->api->transactions()->willReturn($transactions);

        $paymentModel->setDetails(
            $this->getExpectedDetails(transaction_id: 'abcd', result: 'success', status: 'pending', payment_url: 'http://example.com')
        );

        $subject = $this->createTestSubject();

        $subject->execute($request->reveal());
    }

    public function test_it_marks_payment_as_failed_if_tpay_throws_an_exception(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $request = $this->prophesize(PayWithCard::class);
        $details = [
            'tpay' => [
                'card' => 'test-card',
                'transaction_id' => 'abcd',
            ],
        ];

        $paymentModel = $this->prophesize(PaymentInterface::class);
        $paymentModel->getOrder()->willReturn($order->reveal());
        $paymentModel->getDetails()->willReturn($details);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request->getModel()->willReturn($paymentModel->reveal());
        $request->getToken()->willReturn($token->reveal());

        $transactions = $this->prophesize(TransactionsApi::class);

        $this->mapper->getPayload(Argument::type(PaymentDetails::class), $paymentModel->reveal())->willReturn(['GENERATED' => 'PAYLOAD']);
        $transactions->createPaymentByTransactionId(['GENERATED' => 'PAYLOAD'], $details['tpay']['transaction_id'])->willThrow(new TpayException('Something went wrong'));

        $this->api->transactions()->willReturn($transactions);

        $paymentModel->setDetails(
            $this->getExpectedDetails(status: 'failed', transaction_id: 'abcd')
        )->shouldBeCalled();

        $subject = $this->createTestSubject();

        $subject->execute($request->reveal());
    }

    public function test_it_marks_payment_as_processing_if_tpay_does_not_answer(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $request = $this->prophesize(PayWithCard::class);
        $details = [
            'tpay' => [
                'card' => 'test-card',
                'transaction_id' => 'abcd',
            ],
        ];

        $paymentModel = $this->prophesize(PaymentInterface::class);
        $paymentModel->getOrder()->willReturn($order->reveal());
        $paymentModel->getDetails()->willReturn($details);

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request->getModel()->willReturn($paymentModel->reveal());
        $request->getToken()->willReturn($token->reveal());

        $transactions = $this->prophesize(TransactionsApi::class);

        $this->mapper->getPayload(Argument::type(PaymentDetails::class), $paymentModel->reveal())->willReturn(['GENERATED' => 'PAYLOAD']);
        $transactions->createPaymentByTransactionId(['GENERATED' => 'PAYLOAD'], $details['tpay']['transaction_id'])->willThrow(new TpayException('Unknown error response code 0'));

        $this->api->transactions()->willReturn($transactions);

        $paymentModel->setDetails(
            $this->getExpectedDetails(status: 'processing', transaction_id: 'abcd')
        )->shouldBeCalled();

        $subject = $this->createTestSubject();

        $subject->execute($request->reveal());
    }

    public function test_it_marks_a_payment_status_as_failed_once_a_transaction_status_is_failed(): void
    {
        $details = [
            'tpay' => [
                'card' => 'test-card',
                'transaction_id' => 'abcd',
            ],
        ];

        $order = $this->prophesize(OrderInterface::class);
        $order->getLocaleCode()->willReturn('en_US');

        $paymentModel = $this->prophesize(PaymentInterface::class);
        $paymentModel->getOrder()->willReturn($order->reveal());
        $paymentModel->getDetails()->willReturn($details);
        $paymentModel->setDetails(
            $this->getExpectedDetails(transaction_id: 'abcd', status: 'failed'),
        )->shouldBeCalled();

        $token = $this->prophesize(TokenInterface::class);
        $token->getGatewayName()->willReturn('tpay');

        $request = $this->prophesize(PayWithCard::class);
        $request->getToken()->willReturn($token->reveal());
        $request->getModel()->willReturn($paymentModel->reveal());

        $response = [
            'result' => 'failed',
        ];

        $this->mapper->getPayload(Argument::type(PaymentDetails::class), $paymentModel->reveal())->willReturn(['GENERATED' => 'PAYLOAD']);

        $transactions = $this->prophesize(TransactionsApi::class);
        $transactions->createPaymentByTransactionId(['GENERATED' => 'PAYLOAD'], $details['tpay']['transaction_id'])->willReturn($response);

        $this->api->transactions()->willReturn($transactions);

        $subject = $this->createTestSubject();

        $subject->execute($request->reveal());
    }


    private function createTestSubject(): PayWithCardAction
    {
        $action = new PayWithCardAction($this->mapper->reveal());

        $action->setApi($this->api->reveal());

        return $action;
    }
}
