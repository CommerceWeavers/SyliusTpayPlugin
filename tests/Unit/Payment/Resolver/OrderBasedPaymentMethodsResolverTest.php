<?php

declare(strict_types=1);


namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Resolver;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker\PaymentMethodSupportedForOrderCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Payment\Resolver\OrderBasedPaymentMethodsResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webmozart\Assert\InvalidArgumentException;

final class OrderBasedPaymentMethodsResolverTest extends TestCase
{
    use ProphecyTrait;

    private PaymentMethodsResolverInterface|ObjectProphecy $paymentMethodResolver;

    private PaymentMethodSupportedForOrderCheckerInterface|ObjectProphecy $paymentMethodSupportedForOrderChecker;

    private CorePaymentInterface|ObjectProphecy $subject;

    protected function setUp(): void
    {
        $this->paymentMethodResolver = $this->prophesize(PaymentMethodsResolverInterface::class);
        $this->paymentMethodSupportedForOrderChecker = $this->prophesize(PaymentMethodSupportedForOrderCheckerInterface::class);
        $this->subject = $this->prophesize(CorePaymentInterface::class);
    }

    public function test_it_gets_order_based_supported_methods(): void
    {
        $firstPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $secondPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $thirdPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $order = $this->prophesize(OrderInterface::class);
        $this->paymentMethodResolver
            ->getSupportedMethods($this->subject)
            ->willReturn([
                $firstPaymentMethod->reveal(),
                $secondPaymentMethod->reveal(),
                $thirdPaymentMethod->reveal(),
            ])
        ;
        $this->paymentMethodResolver->supports($this->subject)->willReturn(true);
        $this->subject->getOrder()->willReturn($order);
        $this->paymentMethodSupportedForOrderChecker
            ->isSupportedForOrder($firstPaymentMethod, $order)
            ->willReturn(true)
        ;
        $this->paymentMethodSupportedForOrderChecker
            ->isSupportedForOrder($secondPaymentMethod, $order)
            ->willReturn(false)
        ;
        $this->paymentMethodSupportedForOrderChecker
            ->isSupportedForOrder($thirdPaymentMethod, $order)
            ->willReturn(true)
        ;

        $result = $this
            ->createTestSubject()
            ->getSupportedMethods($this->subject->reveal())
        ;

        $this->assertCount(2, $result);
        $this->assertSame($firstPaymentMethod->reveal(), $result[0]);
        $this->assertSame($thirdPaymentMethod->reveal(), $result[1]);
    }

    public function test_it_throws_exception_if_subject_order_is_null(): void
    {
        $firstPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $secondPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $thirdPaymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $this->paymentMethodResolver
            ->getSupportedMethods($this->subject)
            ->willReturn([
                $firstPaymentMethod->reveal(),
                $secondPaymentMethod->reveal(),
                $thirdPaymentMethod->reveal(),
            ])
        ;
        $this->paymentMethodResolver->supports($this->subject)->willReturn(true);
        $this->subject->getOrder()->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $this
            ->createTestSubject()
            ->getSupportedMethods($this->subject->reveal())
        ;
    }

    public function test_it_throws_exception_if_resolver_is_not_supported(): void
    {
        $this->paymentMethodResolver->supports($this->subject)->willReturn(false);

        $this->expectException(InvalidArgumentException::class);

        $this
            ->createTestSubject()
            ->getSupportedMethods($this->subject->reveal())
        ;
    }

    public function test_it_throws_exception_if_subject_is_not_core_payment(): void
    {
        $subject = $this->prophesize(PaymentInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $this
            ->createTestSubject()
            ->getSupportedMethods($subject->reveal())
        ;
    }

    private function createTestSubject(): OrderBasedPaymentMethodsResolver
    {
        return new OrderBasedPaymentMethodsResolver(
            $this->paymentMethodResolver->reveal(),
            $this->paymentMethodSupportedForOrderChecker->reveal()
        );
    }
}
