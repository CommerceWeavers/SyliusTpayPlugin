<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Refunding\Provider;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Provider\TpayAwareRefundPaymentMethodsProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;


final class TpayAwareRefundPaymentMethodsProviderTest extends TestCase
{
    use ProphecyTrait;

    private RefundPaymentMethodsProviderInterface|ObjectProphecy $inner;

    protected function setUp(): void
    {
        $this->inner = $this->prophesize(RefundPaymentMethodsProviderInterface::class);
    }

    public function test_it_returns_inner_methods_when_no_last_completed_payment(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_COMPLETED)->willReturn(null);

        $methods = [$this->createMethod('methodA'), $this->createMethod('methodB')];
        $this->inner->findForOrder($order->reveal())->willReturn($methods);

        $provider = new TpayAwareRefundPaymentMethodsProvider($this->inner->reveal());

        self::assertSame($methods, $provider->findForOrder($order->reveal()));
    }

    public function test_it_returns_inner_methods_when_last_payment_has_transaction_id(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_COMPLETED)->willReturn($payment->reveal());

        $payment->getDetails()->willReturn(['tpay' => ['transaction_id' => 'tr_123']]);

        $methods = [$this->createMethod('methodA'), $this->createMethod('methodB')];
        $this->inner->findForOrder($order->reveal())->willReturn($methods);

        $provider = new TpayAwareRefundPaymentMethodsProvider($this->inner->reveal());

        self::assertSame($methods, $provider->findForOrder($order->reveal()));
    }

    public function test_it_filters_out_tpay_redirect_when_no_transaction_id(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_COMPLETED)->willReturn($payment->reveal());

        $payment->getDetails()->willReturn(['tpay' => []]);

        $tpayRedirectMethod = $this->createMethod('tpay_redirect');
        $otherMethod = $this->createMethod('foo');

        $this->inner->findForOrder($order->reveal())
            ->willReturn([$tpayRedirectMethod, $otherMethod]);

        $provider = new TpayAwareRefundPaymentMethodsProvider($this->inner->reveal());

        self::assertSame([$otherMethod], $provider->findForOrder($order->reveal()));
    }

    private function createMethod(string $factoryName): PaymentMethodInterface
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn($factoryName);

        $method = $this->prophesize(PaymentMethodInterface::class);
        $method->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        return $method->reveal();
    }
}
