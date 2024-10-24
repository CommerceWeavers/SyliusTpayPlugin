<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\GetStatusAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class GetStatusActionTest extends TestCase
{
    use ProphecyTrait;

    private PaymentInterface|ObjectProphecy $model;

    private GatewayInterface|ObjectProphecy $gateway;

    protected function setUp(): void
    {
        $request = $this->prophesize(Notify::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->gateway = $this->prophesize(GatewayInterface::class);

        $request->getFirstModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_get_status_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new GetStatus($this->model->reveal())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new GetStatus(new \stdClass())));
        $this->assertTrue($action->supports(new GetStatus($this->model->reveal())));
    }

    /**
     * @dataProvider data_provider_it_converts_tpay_notification_status
     */
    public function test_it_marks_payment_status_based_on_the_tpay_status(string $status, string $expectedState): void
    {
        $this->model->getDetails()->willReturn(['tpay' => ['status' => $status]]);

        $request = new GetStatus($this->model->reveal());

        $this->createTestSubject()->execute($request);

        $this->assertSame($expectedState, $request->getValue());
    }

    public static function data_provider_it_converts_tpay_notification_status(): iterable
    {
        yield ['correct', PaymentInterface::STATE_COMPLETED];
        yield ['pending', PaymentInterface::STATE_PROCESSING];
        yield ['refund', PaymentInterface::STATE_REFUNDED];
        yield ['failed', PaymentInterface::STATE_FAILED];
    }

    private function createTestSubject(): GetStatusAction
    {
        $action = new GetStatusAction();

        $action->setGateway($this->gateway->reveal());

        return $action;
    }
}
