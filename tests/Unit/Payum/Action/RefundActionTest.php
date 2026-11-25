<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\RefundAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Exception\RefundCannotBeMadeException;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class RefundActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $api;

    private Refund|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->request = $this->prophesize(Refund::class);
        $this->model = $this->prophesize(PaymentInterface::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_refund_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new Refund($this->model->reveal())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Refund(new \stdClass())));
        $this->assertTrue($action->supports(new Refund($this->model->reveal())));
    }

    public function test_it_throws_an_exception_when_tpay_transaction_id_cannot_be_found(): void
    {
        $this->expectException(RefundCannotBeMadeException::class);
        $this->expectExceptionMessage('Tpay transaction id cannot be found.');

        $this->model->getDetails()->willReturn([]);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_creates_a_refund(): void
    {
        $this->model->getDetails()->willReturn(['tpay' => ['transaction_id' => 'tr4ns4ct!0n']]);

        $transactionApi = $this->prophesize(TransactionsApi::class);
        $transactionApi->createRefundByTransactionId([], 'tr4ns4ct!0n')->shouldBeCalled()->willReturn([]);

        $this->api->transactions()->willReturn($transactionApi);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_throws_an_exception_when_tpay_rejects_the_refund(): void
    {
        $this->expectException(RefundCannotBeMadeException::class);
        $this->expectExceptionMessage('No sufficient funds or the refund limit has been reached');

        $this->model->getDetails()->willReturn(['tpay' => ['transaction_id' => 'tr4ns4ct!0n']]);

        $transactionApi = $this->prophesize(TransactionsApi::class);
        $transactionApi->createRefundByTransactionId([], 'tr4ns4ct!0n')->willReturn([
            'result' => 'failed',
            'requestId' => 'b5c1e1ba6f5e0d8a4c2f',
            'errors' => [
                [
                    'errorCode' => 'no_sufficient_funds_or_refund_limit_reached',
                    'errorMessage' => 'No sufficient funds or the refund limit has been reached',
                    'fieldName' => '',
                    'devMessage' => '',
                    'docUrl' => '',
                ],
            ],
        ]);

        $this->api->transactions()->willReturn($transactionApi);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    private function createTestSubject(): RefundAction
    {
        $action = new RefundAction();

        $action->setApi($this->api->reveal());

        return $action;
    }
}
