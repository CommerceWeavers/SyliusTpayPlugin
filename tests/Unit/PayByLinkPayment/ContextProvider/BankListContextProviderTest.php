<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\PayByLinkPayment\ContextProvider;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\ContextProvider\BankListContextProvider;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory as PayByLinkGatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\OrderAwareValidTpayChannelListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\ValidTpayChannelListProviderInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class BankListContextProviderTest extends TestCase
{
    use ProphecyTrait;

    private ValidTpayChannelListProviderInterface|ObjectProphecy $validTpayChannelListProvider;

    private OrderAwareValidTpayChannelListProviderInterface|ObjectProphecy $orderAwareValidTpayChannelListProvider;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->validTpayChannelListProvider = $this->prophesize(ValidTpayChannelListProviderInterface::class);
        $this->orderAwareValidTpayChannelListProvider = $this->prophesize(OrderAwareValidTpayChannelListProviderInterface::class);
        $this->cypher = $this->prophesize(CypherInterface::class);
    }

    public function test_it_does_not_support_some_template_event_and_pay_by_link_template_name(): void
    {
        $templateBlock = new TemplateBlock('pay_by_link', 'sylius.shop.checkout.some_template', null, null, null, null);

        $supports = $this->createTestObject()->supports($templateBlock);

        $this->assertFalse($supports);
    }

    public function test_it_supports_complete_summary_template_event_and_pay_by_link_template_name(): void
    {
        $templateBlock = new TemplateBlock('pay_by_link', 'sylius.shop.checkout.complete.summary', null, null, null, null);

        $supports = $this->createTestObject()->supports($templateBlock);

        $this->assertTrue($supports);
    }

    public function test_it_supports_select_payment_choice_item_form_template_event_and_pay_by_link_template_name(): void
    {
        $templateBlock = new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null);

        $supports = $this->createTestObject()->supports($templateBlock);

        $this->assertTrue($supports);
    }

    public function test_it_provides_banks_in_template_context_if_method_is_present_in_context(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfig::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn(PayByLinkGatewayFactory::NAME);
        $gatewayConfig->getConfig()->willReturn([]);
        $this->orderAwareValidTpayChannelListProvider->provide(Argument::any())->shouldNotBeCalled();
        $this->validTpayChannelListProvider->provide()->willReturn(['3' => 'somebank']);

        $result = $this
            ->createTestObject()
            ->provide(
                ['method' => $paymentMethod->reveal()],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this->assertSame(
            [
                'method' => $paymentMethod->reveal(),
                'defaultTpayChannelId' => null,
                'banks' => ['3' => 'somebank'],
            ],
            $result,
        );
    }

    public function test_it_provides_banks_in_template_context_if_order_is_present_in_context(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfig::class);
        $order->getLastPayment()->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn(PayByLinkGatewayFactory::NAME);
        $gatewayConfig->getConfig()->willReturn([]);
        $this->validTpayChannelListProvider->provide()->shouldNotBeCalled();
        $this->orderAwareValidTpayChannelListProvider->provide($order)->willReturn(['3' => 'somebank']);

        $result = $this
            ->createTestObject()
            ->provide(
                ['order' => $order->reveal()],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this->assertSame(
            [
                'order' => $order->reveal(),
                'defaultTpayChannelId' => null,
                'banks' => ['3' => 'somebank'],
            ],
            $result,
        );
    }

    public function test_it_provides_empty_banks_and_channel_id_in_template_context_if_orders_payment_does_not_have_method(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $order->getLastPayment()->willReturn($payment);
        $payment->getMethod()->willReturn(null);

        $result = $this
            ->createTestObject()
            ->provide(
                ['order' => $order->reveal()],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $this->assertSame(
            [
                'order' => $order->reveal(),
                'defaultTpayChannelId' => null,
                'banks' => [],
            ],
            $result,
        );
    }

    public function test_it_provides_empty_banks_and_channel_id_in_template_context_if_order_does_not_have_last_payment(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment()->willReturn(null);

        $result = $this
            ->createTestObject()
            ->provide(
                ['order' => $order->reveal()],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $this->assertSame(
            [
                'order' => $order->reveal(),
                'defaultTpayChannelId' => null,
                'banks' => [],
            ],
            $result,
        );
    }

    public function test_it_provides_empty_banks_and_channel_id_in_template_context_if_neither_order_nor_method_is_present_in_context(): void
    {
        $result = $this
            ->createTestObject()
            ->provide(
                [],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $this->assertSame(
            [
                'defaultTpayChannelId' => null,
                'banks' => [],
            ],
            $result,
        );
    }

    public function test_it_provides_channel_id_and_empty_banks_in_template_context_if_tpay_channel_id_is_specified(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfig::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn(PayByLinkGatewayFactory::NAME);
        $gatewayConfig->getConfig()->willReturn(['tpay_channel_id' => '3']);

        $result = $this
            ->createTestObject()
            ->provide(
                ['method' => $paymentMethod->reveal()],
                new TemplateBlock('pay_by_link', 'cw.tpay.shop.select_payment.choice_item_form', null, null, null, null),
            )
        ;

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this->validTpayChannelListProvider->provide()->shouldNotBeCalled();
        $this->orderAwareValidTpayChannelListProvider->provide(Argument::any())->shouldNotBeCalled();
        $this->assertSame(
            [
                'method' => $paymentMethod->reveal(),
                'defaultTpayChannelId' => '3',
                'banks' => [],
            ],
            $result,
        );
    }

    private function createTestObject(): BankListContextProvider
    {
        return new BankListContextProvider(
            $this->validTpayChannelListProvider->reveal(),
            $this->orderAwareValidTpayChannelListProvider->reveal(),
            $this->cypher->reveal(),
        );
    }
}
