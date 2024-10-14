<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payum\Action\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Action\Api\CreateVisaMobileTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateVisaMobilePaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;

class CreateVisaMobileTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $tpayApi;

    private CreateVisaMobilePaymentPayloadFactoryInterface|ObjectProphecy $createVisaMobilePaymentPayloadFactory;

    private GenericTokenFactoryInterface|ObjectProphecy $tokenFactoryInterface;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactoryInterface;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createRedirectBasedPaymentPayloadFactory = $this->prophesize(CreateVisaMobilePaymentPayloadFactoryInterface::class);
        $this->tokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_payment_model(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getDetails()->willReturn([]);

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

    private function createTestSubject(): CreateVisaMobileTransactionAction
    {
        $action = new CreateVisaMobileTransactionAction(
            $this->createVisaMobilePaymentPayloadFactory->reveal(),
            $this->notifyTokenFactoryInterface->reveal(),
        );

        $action->setApi($this->api->reveal());
        $action->setGenericTokenFactory($this->tokenFactory->reveal());

        return $action;
    }
}
