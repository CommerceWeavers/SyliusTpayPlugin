<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\PayByLinkPayment\Checker;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker\PaymentMethodSupportedForOrderChecker;
use CommerceWeavers\SyliusTpayPlugin\Tpay\GatewayName;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProviderInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodSupportedForOrderCheckerTest extends TestCase
{
    use ProphecyTrait;

    private CypherInterface|ObjectProphecy $cypher;

    private OrderAwareValidTpayChannelListProviderInterface|ObjectProphecy $orderAwareValidTpayChannelListProvider;

    private PaymentMethodInterface|ObjectProphecy $paymentMethod;

    private OrderInterface|ObjectProphecy $order;

    protected function setUp(): void
    {
        $this->cypher = $this->prophesize(CypherInterface::class);
        $this->orderAwareValidTpayChannelListProvider = $this->prophesize(OrderAwareValidTpayChannelListProviderInterface::class);
        $this->paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $this->order = $this->prophesize(OrderInterface::class);
    }

    public function test_it_returns_true_if_gateway_config_is_null(): void
    {
        $this->paymentMethod->getGatewayConfig()->willReturn(null);

        $result = $this
            ->createTestSubject()
            ->isSupportedForOrder($this->paymentMethod->reveal(), $this->order->reveal())
        ;

        $this->assertTrue($result);
    }

    public function test_it_returns_true_if_gateway_config_factory_name_is_not_pay_by_link(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getFactoryName()->willReturn('i_am_not_pay_by_link');

        $result = $this
            ->createTestSubject()
            ->isSupportedForOrder($this->paymentMethod->reveal(), $this->order->reveal())
        ;

        $this->assertTrue($result);
    }

    public function test_it_returns_true_if_gateway_config_does_not_have_tpay_channel_id(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);
        $gatewayConfig->getConfig()->willReturn([]);

        $result = $this
            ->createTestSubject()
            ->isSupportedForOrder($this->paymentMethod->reveal(), $this->order->reveal())
        ;

        $this->assertTrue($result);
    }

    public function test_it_returns_true_if_tpay_channel_id_is_valid(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);
        $gatewayConfig->getConfig()->willReturn(['tpay_channel_id' => 21]);
        $this->orderAwareValidTpayChannelListProvider
            ->provide($this->order)
            ->willReturn([19 => [], 21 => [], 22 => []])
        ;

        $result = $this
            ->createTestSubject()
            ->isSupportedForOrder($this->paymentMethod->reveal(), $this->order->reveal())
        ;

        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_tpay_channel_id_is_not_valid(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getFactoryName()->willReturn(GatewayName::PAY_BY_LINK);
        $gatewayConfig->getConfig()->willReturn(['tpay_channel_id' => 21]);
        $this->orderAwareValidTpayChannelListProvider
            ->provide($this->order)
            ->willReturn([19 => [], 22 => []])
        ;

        $result = $this
            ->createTestSubject()
            ->isSupportedForOrder($this->paymentMethod->reveal(), $this->order->reveal())
        ;

        $this->assertFalse($result);
    }

    private function createTestSubject(): PaymentMethodSupportedForOrderChecker
    {
        return new PaymentMethodSupportedForOrderChecker(
            $this->cypher->reveal(),
            $this->orderAwareValidTpayChannelListProvider->reveal(),
        );
    }
}
