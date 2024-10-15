<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\BasicPaymentFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\ChecksumVerifierInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use tpaySDK\Model\Objects\NotificationBody\BasicPayment;

final class NotifyActionTest extends TestCase
{
    use ProphecyTrait;

    private Notify|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    private TpayApi|ObjectProphecy $api;

    private BasicPaymentFactoryInterface|ObjectProphecy $basicPaymentFactory;

    private ChecksumVerifierInterface|ObjectProphecy $checksumVerifier;

    private SignatureVerifierInterface|ObjectProphecy $signatureVerifier;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(Notify::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->api = $this->prophesize(TpayApi::class);
        $this->basicPaymentFactory = $this->prophesize(BasicPaymentFactoryInterface::class);
        $this->checksumVerifier = $this->prophesize(ChecksumVerifierInterface::class);
        $this->signatureVerifier = $this->prophesize(SignatureVerifierInterface::class);

        $this->request->getModel()->willReturn($this->model->reveal());
    }

    public function test_it_supports_only_notify_requests(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Sync($this->model->reveal())));
        $this->assertTrue($action->supports(new Notify($this->model->reveal(), $this->createNotifyDataObject())));
    }

    public function test_it_supports_only_payment_interface_based_models(): void
    {
        $action = $this->createTestSubject();

        $this->assertFalse($action->supports(new Notify(new \stdClass(), $this->createNotifyDataObject())));
        $this->assertTrue($action->supports(new Notify($this->model->reveal(), $this->createNotifyDataObject())));
    }

    /**
     * @dataProvider data_provider_it_converts_tpay_notification_status
     */
    public function test_it_converts_tpay_notification_status(string $status, string $expectedStatus): void
    {
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            'content',
            [
                'tr_status' => $status,
            ],
        ));

        $this->api->getNotificationSecretCode()->willReturn('merchant_code');

        $this->basicPaymentFactory->createFromArray(['tr_status' => $status])->willReturn($basicPayment = new BasicPayment());
        $basicPayment->tr_status = $status;

        $this->checksumVerifier->verify($basicPayment, 'merchant_code')->willReturn(true);
        $this->signatureVerifier->verify('jws', 'content')->willReturn(true);

        $this->model->getDetails()->willReturn([]);
        $this->model->setDetails([
            'tpay' => [
                'transaction_id' => null,
                'result' => null,
                'status' => $expectedStatus,
                'apple_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'blik_token' => null,
                'google_pay_token' => null,
                'card' => null,
                'payment_url' => null,
                'success_url' => null,
                'failure_url' => null,
                'tpay_channel_id' => null,
                'visa_mobile_phone_number' => null,
            ],
        ])->shouldBeCalled();

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_throws_false_http_reply_when_checksum_is_invalid(): void
    {
        $this->model->getDetails()->willReturn([]);
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            'content',
            [
                'tr_status' => 'TRUE',
            ],
        ));

        $this->api->getNotificationSecretCode()->willReturn('merchant_code');

        $this->basicPaymentFactory->createFromArray(['tr_status' => 'TRUE'])->willReturn($basicPayment = new BasicPayment());
        $basicPayment->tr_status = 'TRUE';

        $this->checksumVerifier->verify($basicPayment, 'merchant_code')->willReturn(false);
        $this->signatureVerifier->verify('jws', 'content')->willReturn(true);

        $this->expectException(HttpResponse::class);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_throws_false_http_reply_when_signature_is_invalid(): void
    {
        $this->model->getDetails()->willReturn([]);
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            'content',
            [
                'tr_status' => 'TRUE',
            ],
        ));

        $this->api->getNotificationSecretCode()->willReturn('merchant_code');

        $this->basicPaymentFactory->createFromArray(['tr_status' => 'TRUE'])->willReturn($basicPayment = new BasicPayment());
        $basicPayment->tr_status = 'TRUE';

        $this->checksumVerifier->verify($basicPayment, 'merchant_code')->willReturn(true);
        $this->signatureVerifier->verify('jws', 'content')->willReturn(false);

        $this->expectException(HttpResponse::class);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public static function data_provider_it_converts_tpay_notification_status(): iterable
    {
        yield 'status containing the `TRUE` word' => ['TRUE', PaymentInterface::STATE_COMPLETED];
        yield 'status containing the other than `TRUE` word' => ['FALSE', PaymentInterface::STATE_FAILED];
        yield 'status containing the `CHARGEBACK` word' => ['CHARGEBACK', PaymentInterface::STATE_REFUNDED];
    }

    private function createNotifyDataObject(string $jws = 'jws', string $content = 'content', array $parameters = []): NotifyData
    {
        return new NotifyData($jws, $content, $parameters);
    }

    private function createTestSubject(): NotifyAction
    {
        $action = new NotifyAction(
            $this->basicPaymentFactory->reveal(),
            $this->checksumVerifier->reveal(),
            $this->signatureVerifier->reveal(),
        );

        $action->setApi($this->api->reveal());

        return $action;
    }
}
