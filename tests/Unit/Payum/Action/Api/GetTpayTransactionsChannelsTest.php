<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\GetTpayTransactionsChannelsAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\GetTpayTransactionsChannels;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use Payum\Core\Model\PaymentInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Api\Transactions\TransactionsApi;

final class GetTpayTransactionsChannelsTest extends TestCase
{
    use ProphecyTrait;

    private GetTpayTransactionsChannels|ObjectProphecy $request;
    private TpayApi|ObjectProphecy $api;
    private PaymentInterface|ObjectProphecy $model;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(GetTpayTransactionsChannels::class);
        $this->api = $this->prophesize(TpayApi::class);
        $this->model = $this->prophesize(PaymentInterface::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_get_bank_groups_request(): void
    {
        $action = new GetTpayTransactionsChannelsAction();

        $this->assertFalse($action->supports(new Notify(new \stdClass(), new \ArrayObject())));
        $this->assertTrue($action->supports(new GetTpayTransactionsChannels($this->model->reveal())));
    }

    public function test_it_returns_bank_groups_on_execute(): void
    {
        $transactions = $this->prophesize(TransactionsApi::class);

        $this->api->transactions()->willReturn($transactions);

        $channels = [];

        $transactions->getChannels()->willReturn($channels);
        $this->request->setResult($channels)->shouldBeCalled();

        $action = new GetTpayTransactionsChannelsAction();
        $action->setApi($this->api->reveal());
        $action->execute($this->request->reveal());
    }

}
