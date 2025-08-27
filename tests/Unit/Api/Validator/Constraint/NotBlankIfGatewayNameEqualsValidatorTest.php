<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayNameEquals;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayNameEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class NotBlankIfGatewayNameEqualsValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private ObjectProphecy|OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_unsupported_constraint_passed(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayNameEquals", "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility" given');

        $this->validator->validate('foo', new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_gateway_name_is_not_passed(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('Option "gatewayName" must be given for constraint "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayNameEqualsValidator".');

        $this->validator->validate('foo', new NotBlankIfGatewayNameEquals());
    }

    public function test_it_throws_an_exception_if_a_context_object_is_unsupported(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay", "null" given');

        $this->validator->validate(new \stdClass(), new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));
    }

    public function test_it_does_nothing_if_value_is_integer(): void
    {
        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $context = $this->prophesize(ExecutionContextInterface::class);
        $context->getObject()->willReturn($pay);

        $this->validator->initialize($context->reveal());
        $this->validator->validate(5, new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_value_is_bool(): void
    {
        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $context = $this->prophesize(ExecutionContextInterface::class);
        $context->getObject()->willReturn($pay);

        $this->validator->initialize($context->reveal());
        $this->validator->validate(true, new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_value_is_not_an_empty_string(): void
    {
        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $context = $this->prophesize(ExecutionContextInterface::class);
        $context->getObject()->willReturn($pay);

        $this->validator->initialize($context->reveal());
        $this->validator->validate('test', new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_gateway_name_for_a_given_order_is_missing(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn(null);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $context = $this->prophesize(ExecutionContextInterface::class);
        $context->getObject()->willReturn($pay);

        $this->validator->initialize($context->reveal());
        $this->validator->validate('test', new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_gateway_names_are_different(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('foo');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn($payment);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $context = $this->prophesize(ExecutionContextInterface::class);
        $context->getObject()->willReturn($pay);

        $this->validator->initialize($context->reveal());
        $this->validator->validate('test', new NotBlankIfGatewayNameEquals(gatewayName: 'bar'));

        $this->assertNoViolation();
    }

    public function test_it_adds_a_violation_if_value_is_null_and_gateway_names_matches(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('foo');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn($payment);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->setObject($pay);

        $this->validator->validate(null, new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this
            ->buildViolation('commerce_weavers_sylius_tpay.shop.pay.field.not_blank')
            ->setCode(NotBlankIfGatewayNameEquals::FIELD_REQUIRED_ERROR)
            ->assertRaised()
        ;
    }

    public function test_it_adds_a_violation_if_value_is_an_empty_string_and_gateway_names_matches(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getFactoryName()->willReturn('foo');

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn($payment);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->setObject($pay);

        $this->validator->validate('', new NotBlankIfGatewayNameEquals(gatewayName: 'foo'));

        $this
            ->buildViolation('commerce_weavers_sylius_tpay.shop.pay.field.not_blank')
            ->setCode(NotBlankIfGatewayNameEquals::FIELD_REQUIRED_ERROR)
            ->assertRaised()
        ;
    }

    protected function createValidator(): NotBlankIfGatewayNameEqualsValidator
    {
        return new NotBlankIfGatewayNameEqualsValidator($this->orderRepository->reveal());
    }
}
