<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\NotifyAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasRegister;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyAliasUnregister;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\NotifyTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

final class NotifyActionTest extends TestCase
{
    use ProphecyTrait;

    private Notify|ObjectProphecy $request;

    private PaymentInterface|ObjectProphecy $model;

    private SignatureVerifierInterface|ObjectProphecy $signatureVerifier;

    private GatewayInterface|ObjectProphecy $gateway;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(Notify::class);
        $this->model = $this->prophesize(PaymentInterface::class);
        $this->signatureVerifier = $this->prophesize(SignatureVerifierInterface::class);
        $this->gateway = $this->prophesize(GatewayInterface::class);

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

    public function test_it_executes_notify_transaction_request(): void
    {
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            'content',
            [
                'tr_status' => 'anything',
            ],
        ));

        $this->signatureVerifier->verify('jws', 'content')->willReturn(true);

        $this->gateway->execute(Argument::type(NotifyTransaction::class))->shouldBeCalled();

        $this->createTestSubject()->execute($this->request->reveal());
    }

    public function test_it_executes_notify_alias_register_request(): void
    {
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            '{"event":"ALIAS_REGISTER"}',
            [],
        ));

        $this->signatureVerifier->verify('jws', '{"event":"ALIAS_REGISTER"}')->willReturn(true);

        $this->gateway->execute(Argument::type(NotifyAliasRegister::class))->shouldBeCalled();

        $this->createTestSubject()->execute($this->request->reveal());
    }

    /** @dataProvider unregisterEventsDataProvider */
    public function test_it_executes_notify_alias_unregister_request(string $event): void
    {
        $this->request->getData()->willReturn(new NotifyData(
            'jws',
            $content = sprintf('{"event":"%s"}', $event),
            [],
        ));

        $this->signatureVerifier->verify('jws', $content)->willReturn(true);

        $this->gateway->execute(Argument::type(NotifyAliasUnregister::class))->shouldBeCalled();

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


        $this->signatureVerifier->verify('jws', 'content')->willReturn(false);

        $this->expectException(HttpResponse::class);

        $this->createTestSubject()->execute($this->request->reveal());
    }

    private function createNotifyDataObject(string $jws = 'jws', string $content = 'content', array $parameters = []): NotifyData
    {
        return new NotifyData($jws, $content, $parameters);
    }

    private function createTestSubject(): NotifyAction
    {
        $action = new NotifyAction(
            $this->signatureVerifier->reveal(),
        );

        $action->setGateway($this->gateway->reveal());

        return $action;
    }

    private function unregisterEventsDataProvider(): array
    {
        return [
            ['ALIAS_UNREGISTER'],
            ['ALIAS_EXPIRED'],
        ];
    }
}
