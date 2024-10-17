<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\BasicPaymentFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\ChecksumVerifierInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use tpaySDK\Model\Objects\NotificationBody\BasicPayment;

final class NotifyTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private BasicPaymentFactoryInterface|ObjectProphecy $basicPaymentFactory;

    private ChecksumVerifierInterface|ObjectProphecy $checksumVerifier;

    private NotifyTransaction|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    private TpayApi|ObjectProphecy $api;

    protected function setUp(): void
    {
        $this->basicPaymentFactory = $this->prophesize(BasicPaymentFactoryInterface::class);
        $this->checksumVerifier = $this->prophesize(ChecksumVerifierInterface::class);
        $this->request = $this->prophesize(NotifyTransaction::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->api = $this->prophesize(TpayApi::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_notify_transaction_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new NotifyTransaction($this->model->reveal(), $this->createNotifyDataObject())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new NotifyTransaction(new \stdClass(), $this->createNotifyDataObject())));
        $this->assertTrue($action->supports(new NotifyTransaction($this->model->reveal(), $this->createNotifyDataObject())));
    }

    /**
     * @dataProvider notificationStatusDataProvider
     */
    public function test_it_converts_tpay_notification_status(string $status, string $expectedStatus): void
    {
        $basicPayment = new BasicPayment();
        $basicPayment->tr_status = $status;
        $this->request->getData()->willReturn($this->createNotifyDataObject());
        $this->model->getDetails()->willReturn([]);
        $this->basicPaymentFactory->createFromArray([])->willReturn($basicPayment);
        $this->api->getNotificationSecretCode()->willReturn('merchant_code');
        $this->checksumVerifier->verify($basicPayment, 'merchant_code')->willReturn(true);

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

    public function test_it_throws_false_http_reply_when_signature_is_invalid(): void
    {
        $basicPayment = new BasicPayment();
        $basicPayment->tr_status = 'TRUE';
        $this->request->getData()->willReturn($this->createNotifyDataObject());
        $this->model->getDetails()->willReturn([]);
        $this->basicPaymentFactory->createFromArray([])->willReturn($basicPayment);
        $this->api->getNotificationSecretCode()->willReturn('merchant_code');
        $this->checksumVerifier->verify($basicPayment, 'merchant_code')->willReturn(false);

        $this->expectException(HttpResponse::class);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_throws_exception_if_notification_secret_code_is_null(): void
    {
        $basicPayment = new BasicPayment();
        $basicPayment->tr_status = 'TRUE';
        $this->request->getData()->willReturn($this->createNotifyDataObject());
        $this->model->getDetails()->willReturn([]);
        $this->basicPaymentFactory->createFromArray([])->willReturn($basicPayment);
        $this->api->getNotificationSecretCode()->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Notification secret code is not set');

        $this->createTestSubject()->execute($this->request->reveal());
    }

    private function notificationStatusDataProvider(): iterable
    {
        yield 'status containing the `TRUE` word' => ['xTRUEd', PaymentInterface::STATE_COMPLETED];
        yield 'status being the `TRUE` word' => ['TRUE', PaymentInterface::STATE_COMPLETED];
        yield 'status containing the other than `TRUE` word' => ['xFALSEyz', PaymentInterface::STATE_FAILED];
        yield 'status not being the `TRUE` word' => ['FALSE', PaymentInterface::STATE_FAILED];
        yield 'status containing the `CHARGEBACK` word' => ['HECHARGEBACKLLO', PaymentInterface::STATE_REFUNDED];
        yield 'status being the `CHARGEBACK` word' => ['CHARGEBACK', PaymentInterface::STATE_REFUNDED];
    }

    private function createNotifyDataObject(string $jws = 'jws', string $content = 'content', array $parameters = []): NotifyData
    {
        return new NotifyData($jws, $content, $parameters);
    }

    private function createTestSubject(): NotifyTransactionAction
    {
        $action = new NotifyTransactionAction(
            $this->basicPaymentFactory->reveal(),
            $this->checksumVerifier->reveal(),
        );

        $action->setApi($this->api->reveal());

        return $action;
    }
}
