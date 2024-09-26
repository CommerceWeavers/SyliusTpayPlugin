<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Tpay\OpenApi\Api\TpayApi;

final class NotifyActionTest extends TestCase
{
    use ProphecyTrait;

    private Notify|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    private TpayApi|ObjectProphecy $api;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(Notify::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->api = $this->prophesize(TpayApi::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_notify_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new Notify($this->model->reveal(), new ArrayObject())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Notify(new \stdClass(), new ArrayObject())));
        $this->assertTrue($action->supports(new Notify($this->model->reveal(), new ArrayObject())));
    }

    /**
     * @dataProvider data_provider_it_converts_tpay_notification_status
     */
    public function test_it_converts_tpay_notification_status(string $status, string $expectedStatus): void
    {
        $this->model->getDetails()->willReturn([]);
        $this->request->getData()->willReturn(new ArrayObject(['tr_status' => $status]));

        $this->model->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => $expectedStatus,
                'blik_token' => null,
                'card' => null,
                'payment_url' => null,
            ],
        ])->shouldBeCalled();

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public static function data_provider_it_converts_tpay_notification_status(): iterable
    {
        yield 'status containing the `TRUE` word' => ['TRUE', PaymentInterface::STATE_COMPLETED];
        yield 'status containing the other than `TRUE` word' => ['FALSE', PaymentInterface::STATE_FAILED];
        yield 'status containing the `CHARGEBACK` word' => ['CHARGEBACK', PaymentInterface::STATE_REFUNDED];
    }

    private function createTestSubject(): NotifyAction
    {
        $action = new NotifyAction();

        $action->setApi($this->api->reveal());

        return $action;
    }
}
