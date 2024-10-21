<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Serializer\ContextBuilder;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\Contract\PaymentIdAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\InitializeApplePaySession;
use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder\AwareContextBuilderInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder\PaymentIdAwareContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class PaymentIdAwareContextBuilderTest extends TestCase
{
    use ProphecyTrait;

    private SerializerContextBuilderInterface|ObjectProphecy $decoratedContextBuilder;

    protected function setUp(): void
    {
        $this->decoratedContextBuilder = $this->prophesize(SerializerContextBuilderInterface::class);
    }

    public function test_it_returns_its_attribute_key(): void
    {
        $this->assertSame('paymentId', $this->createTestSubject()->getAttributeKey());
    }

    public function test_it_returns_supported_interface(): void
    {
        $this->assertSame(PaymentIdAwareInterface::class, $this->createTestSubject()->getSupportedInterface());
    }

    public function test_it_returns_property_name_accessor_method_name(): void
    {
        $this->assertSame('getPaymentIdPropertyName', $this->createTestSubject()->getPropertyNameAccessorMethodName());
    }

    public function test_it_does_not_support_a_request_without_an_input_class(): void
    {
        $isSupported = $this->createTestSubject()->supports($this->prophesize(Request::class)->reveal(), [], null);

        $this->assertFalse($isSupported);
    }

    public function test_it_does_not_support_a_request_with_an_input_class_that_does_not_implement_payment_id_aware_interface(): void
    {
        $context = ['input' => ['class' => \stdClass::class]];

        $isSupported = $this->createTestSubject()->supports($this->prophesize(Request::class)->reveal(), $context, null);

        $this->assertFalse($isSupported);
    }

    public function test_it_returns_a_context_from_a_decorated_service_if_it_does_not_support_the_request(): void
    {
        $request = $this->prophesize(Request::class)->reveal();

        $this->decoratedContextBuilder->createFromRequest($request, true, [])->willReturn(['baz' => 'qux']);

        $result = $this->createTestSubject()->createFromRequest($request, true, []);

        $this->assertSame(['baz' => 'qux'], $result);
    }

    public function test_it_adds_a_payment_id_to_default_constructor_arguments_for_supported_requests(): void
    {
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get('paymentId')->willReturn(1);

        $request = $this->prophesize(Request::class);
        $request->attributes = $attributes->reveal();

        $this->decoratedContextBuilder->createFromRequest($request, true, [])->willReturn(['input' => ['class' => InitializeApplePaySession::class]]);

        $result = $this->createTestSubject()->createFromRequest($request->reveal(), true, []);

        $this->assertSame([
            'input' => [
                'class' => InitializeApplePaySession::class,
            ],
            'default_constructor_arguments' => [
                InitializeApplePaySession::class => ['paymentId' => 1],
            ],
        ], $result);
    }

    private function createTestSubject(): AwareContextBuilderInterface
    {
        return new PaymentIdAwareContextBuilder($this->decoratedContextBuilder->reveal());
    }
}
