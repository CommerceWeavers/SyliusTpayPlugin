<?php declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tests\Unit\GooglePayPayment\Twig\Hooks;

use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\GooglePayPayment\Twig\Hooks\ConfirmOrderButtonHookable;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\TwigHooks\Bag\DataBag;
use Sylius\TwigHooks\Bag\DataBagInterface;
use Sylius\TwigHooks\Bag\ScalarDataBagInterface;
use Sylius\TwigHooks\Hook\Metadata\HookMetadata;
use Sylius\TwigHooks\Hookable\AbstractHookable;
use Sylius\TwigHooks\Hookable\Metadata\HookableMetadata;
use Sylius\TwigHooks\Hookable\Renderer\HookableRendererInterface;

final class ConfirmOrderButtonHookableTest extends TestCase
{
    use ProphecyTrait;

    private HookableRendererInterface|ObjectProphecy $hookableRenderer;

    protected function setUp(): void
    {
        $this->hookableRenderer = $this->prophesize(HookableRendererInterface::class);
    }

    public function test_it_delegates_to_decorated_renderer_when_hookable_id_does_not_match(): void
    {
        $hookable = $this->createHookable('different_hook', 'navigation');
        $metadata = $this->prophesize(HookableMetadata::class);

        $expectedOutput = '<button>Complete Order</button>';
        $this->hookableRenderer
            ->render($hookable, $metadata->reveal())
            ->willReturn($expectedOutput)
            ->shouldBeCalled()
        ;

        $result = $this->createTestSubject()->render($hookable, $metadata->reveal());

        self::assertSame($expectedOutput, $result);
    }

    public function test_it_returns_empty_string_when_payment_method_is_google_pay(): void
    {
        $hookable = $this->createHookable('sylius_shop.checkout.complete.content.form', 'navigation');

        $order = $this->prophesize(OrderLastNewPaymentAwareInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $context = $this->prophesize(DataBagInterface::class);
        $context->offsetGet('resource')->willReturn($order->reveal());

        $metadata = $this->createMetadata(context: $context->reveal());

        $order->getLastCartPayment()->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getFactoryName()->willReturn(GatewayFactory::NAME);

        $this->hookableRenderer
            ->render($hookable, $metadata)
            ->shouldNotBeCalled()
        ;

        $result = $this->createTestSubject()->render($hookable, $metadata);

        self::assertSame('', $result);
    }

    public function test_it_delegates_to_decorated_renderer_when_payment_method_is_not_google_pay(): void
    {
        $hookable = $this->createHookable('sylius_shop.checkout.complete.content.form', 'navigation');

        $order = $this->prophesize(OrderLastNewPaymentAwareInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        $context = $this->prophesize(DataBagInterface::class);
        $context->offsetGet('resource')->willReturn($order->reveal());

        $metadata = $this->createMetadata(context: $context->reveal());

        $order->getLastCartPayment()->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getFactoryName()->willReturn('other_payment_gateway');

        $expectedOutput = '<button>Complete Order</button>';
        $this->hookableRenderer
            ->render($hookable, $metadata)
            ->willReturn($expectedOutput)
            ->shouldBeCalled()
        ;

        $result = $this->createTestSubject()->render($hookable, $metadata);

        self::assertSame($expectedOutput, $result);
    }

    public function test_it_delegates_to_decorated_renderer_when_payment_method_is_null(): void
    {
        $hookable = $this->createHookable('sylius_shop.checkout.complete.content.form', 'navigation');

        $order = $this->prophesize(OrderLastNewPaymentAwareInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $context = $this->prophesize(DataBagInterface::class);
        $context->offsetGet('resource')->willReturn($order->reveal());

        $metadata = $this->createMetadata(context: $context->reveal());

        $order->getLastCartPayment()->willReturn($payment->reveal());
        $payment->getMethod()->willReturn(null);

        $expectedOutput = '<button>Complete Order</button>';
        $this->hookableRenderer
            ->render($hookable, $metadata)
            ->willReturn($expectedOutput)
            ->shouldBeCalled()
        ;

        $result = $this->createTestSubject()->render($hookable, $metadata);

        self::assertSame($expectedOutput, $result);
    }

    public function test_it_delegates_to_decorated_renderer_when_gateway_config_is_null(): void
    {
        $hookable = $this->createHookable('sylius_shop.checkout.complete.content.form', 'navigation');

        $order = $this->prophesize(OrderLastNewPaymentAwareInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);

        $context = $this->prophesize(DataBagInterface::class);
        $context->offsetGet('resource')->willReturn($order->reveal());

        $metadata = $this->createMetadata(context: $context->reveal());

        $order->getLastCartPayment()->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $expectedOutput = '<button>Complete Order</button>';
        $this->hookableRenderer
            ->render($hookable, $metadata)
            ->willReturn($expectedOutput)
            ->shouldBeCalled()
        ;

        $result = $this->createTestSubject()->render($hookable, $metadata);

        self::assertSame($expectedOutput, $result);
    }

    private function createHookable(string $hookName, string $name): AbstractHookable
    {
        return new class($hookName, $name) extends AbstractHookable {
            public function toArray(): array
            {
                return [];
            }
        };
    }

    private function createMetadata(?HookMetadata $hookMetadata = null, ?DataBagInterface $context = null, ?ScalarDataBagInterface $configuration = null): HookableMetadata
    {
        return new HookableMetadata(
            $hookMetadata ?? new HookMetadata('hook', new DataBag()),
            $context ?? $this->prophesize(DataBagInterface::class)->reveal(),
            $configuration ?? $this->prophesize(ScalarDataBagInterface::class)->reveal(),
        );
    }

    private function createTestSubject(): ConfirmOrderButtonHookable
    {
        return new ConfirmOrderButtonHookable($this->hookableRenderer->reveal());
    }
}
