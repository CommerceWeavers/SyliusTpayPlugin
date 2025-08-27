<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayNameEquals;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayNameEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class OneOfPropertiesRequiredIfGatewayNameEqualsValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private ObjectProphecy|OrderRepositoryInterface $orderRepository;

    public function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_unsupported_constraint_passed(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayNameEquals", "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility" given');

        $this->validator->validate('foo', new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_gateway_name_is_not_passed(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('Option "gatewayName" must be given for constraint "CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayNameEqualsValidator".');

        $this->validator->validate('foo', new OneOfPropertiesRequiredIfGatewayNameEquals());
    }

    public function test_it_throws_an_exception_if_a_value_is_unsupported(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay", "stdClass" given');

        $this->validator->validate(new \stdClass(), new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo'));
    }

    public function test_it_does_nothing_if_order_cannot_be_found(): void
    {
        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn(null);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_payment_method_cannot_be_found(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn(null);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_payment_cannot_be_found(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn(null);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_gateway_config_cannot_be_found(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment('new')->willReturn($payment);

        $this->orderRepository->findOneByTokenValue('t0k3n')->willReturn($order);

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo'));

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

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'bar'));

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_gateway_names_are_same_and_all_properties_are_set(): void
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

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com', blikToken: '123456');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo', properties: ['blikToken']));

        $this->assertNoViolation();
    }

    public function test_it_adds_violation_if_gateway_names_are_same_and_any_of_required_properties_is_not_set(): void
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

        $pay = new Pay(orderToken: 't0k3n', successUrl: 'https://success.com', failureUrl: 'https://failure.com', blikToken: '123456');

        $this->validator->validate($pay, new OneOfPropertiesRequiredIfGatewayNameEquals(gatewayName: 'foo', properties: ['googlePayToken']));

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.fields_required')
            ->setCode('c64630dd-3766-4a69-9d83-66aabf8f68fe')
            ->assertRaised()
        ;
    }

    protected function createValidator(): OneOfPropertiesRequiredIfGatewayNameEqualsValidator
    {
        return new OneOfPropertiesRequiredIfGatewayNameEqualsValidator($this->orderRepository->reveal());
    }
}
