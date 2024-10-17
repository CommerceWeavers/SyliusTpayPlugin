<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyTransaction;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use tpaySDK\Model\Objects\NotificationBody\BasicPayment;

final class NotifyTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private NotifyTransaction|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(NotifyTransaction::class);
        $this->model = $this->prophesize(PaymentInterface::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_notify_transaction_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new NotifyTransaction($this->model->reveal(), new BasicPayment())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new NotifyTransaction(new \stdClass(), new BasicPayment())));
        $this->assertTrue($action->supports(new NotifyTransaction($this->model->reveal(), new BasicPayment())));
    }

    /**
     * @dataProvider notificationStatusDataProvider
     */
    public function test_it_converts_tpay_notification_status(string $status, string $expectedStatus): void
    {
        $basicPayment = new BasicPayment();
        $basicPayment->tr_status = $status;
        $this->request->getBasicPayment()->willReturn($basicPayment);
        $this->model->getDetails()->willReturn([]);

        $this->createTestSubject()->execute($this->request->reveal());

        $this->model->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => $expectedStatus,
                'blik_token' => null,
                'blik_save_alias' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
            ],
        ])->shouldBeCalled();
    }

    public static function notificationStatusDataProvider(): iterable
    {
        yield 'status containing the `TRUE` word' => ['xTRUEd', PaymentInterface::STATE_COMPLETED];
        yield 'status being the `TRUE` word' => ['TRUE', PaymentInterface::STATE_COMPLETED];
        yield 'status containing the other than `TRUE` word' => ['xFALSEyz', PaymentInterface::STATE_FAILED];
        yield 'status not being the `TRUE` word' => ['FALSE', PaymentInterface::STATE_FAILED];
        yield 'status containing the `CHARGEBACK` word' => ['HECHARGEBACKLLO', PaymentInterface::STATE_REFUNDED];
        yield 'status being the `CHARGEBACK` word' => ['CHARGEBACK', PaymentInterface::STATE_REFUNDED];
    }

    private function createTestSubject(): NotifyTransactionAction
    {
        return new NotifyTransactionAction();
    }
}
