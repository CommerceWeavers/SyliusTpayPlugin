<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\VisaMobilePayment\Payum\Action;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\CreateTransaction;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateVisaMobilePaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use CommerceWeavers\SyliusTpayPlugin\VisaMobilePayment\Payum\Action\CreateVisaMobileTransactionAction;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class CreateVisaMobileTransactionActionTest extends TestCase
{
    use ProphecyTrait;

    private TpayApi|ObjectProphecy $api;

    private CreateVisaMobilePaymentPayloadFactoryInterface|ObjectProphecy $createVisaMobilePaymentPayloadFactory;

    private GenericTokenFactoryInterface|ObjectProphecy $tokenFactory;

    private NotifyTokenFactoryInterface|ObjectProphecy $notifyTokenFactory;

    protected function setUp(): void
    {
        $this->api = $this->prophesize(TpayApi::class);
        $this->createVisaMobilePaymentPayloadFactory = $this->prophesize(CreateVisaMobilePaymentPayloadFactoryInterface::class);
        $this->tokenFactory = $this->prophesize(GenericTokenFactoryInterface::class);
        $this->notifyTokenFactory = $this->prophesize(NotifyTokenFactoryInterface::class);
    }

    public function test_it_supports_create_transaction_requests_with_a_valid_gateway_name(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('tpay_visa_mobile');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['visa_mobile_phone_number' => '44123456789']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertTrue($isSupported);
    }

    public function test_it_does_not_support_create_transaction_requests_with_an_invalid_gateway_name(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getGatewayName()->willReturn('invalid_gateway_name');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);
        $payment->getDetails()->willReturn(['tpay' => ['visa_mobile_phone_number' => '44123456789']]);

        $request = $this->prophesize(CreateTransaction::class);
        $request->getModel()->willReturn($payment);

        $isSupported = $this->createTestSubject()->supports($request->reveal());

        $this->assertFalse($isSupported);
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
            $this->notifyTokenFactory->reveal(),
        );

        $action->setApi($this->api->reveal());
        $action->setGenericTokenFactory($this->tokenFactory->reveal());

        return $action;
    }
}
