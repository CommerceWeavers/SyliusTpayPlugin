<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEquals;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEqualsValidator;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class NotBlankIfGatewayConfigTypeEqualsValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private OrderRepositoryInterface|ObjectProphecy $orderRepository;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->cypher = $this->prophesize(CypherInterface::class);

        parent::setUp();

        $this->setObject(new Pay('orderToken123', 'http://example.com?success', 'http://example.com?failure'));
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            't00k33n',
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_throws_an_exception_if_a_constraint_has_not_payment_method_type_specified(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(),
        );
    }

    public function test_it_throws_an_exception_if_a_context_object_is_not_valid(): void
    {
        $this->setObject(null);

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );
    }

    /** @dataProvider emptyDataProvider */
    public function test_it_does_not_validate_if_value_is_not_empty(mixed $value): void
    {
        $this->validator->validate(
            $value,
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_order_does_not_exist(): void
    {
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn(null);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_payment_does_not_exist(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_payment_method_does_not_exist(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);
        $payment->getMethod()->willReturn(null);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_gateway_config_does_not_exist(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn(null);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_decrypts_gateway_config_if_it_is_encrypted(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $gatewayConfig->getConfig()->willReturn([]);

        $this->validator->validate(
            '',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
    }

    public function test_it_does_not_validate_if_gateway_config_type_is_different(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn(['type' => 'google_pay']);

        $this->validator->validate(
            't00k33n',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_value_is_empty_and_gateway_config_type_is_as_expected(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn(['type' => 'blik']);

        $this->validator->validate(
            '',
            new NotBlankIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.field.not_blank')
            ->setCode('275416a8-bd6f-4990-96ed-a2da514ce2f9')
            ->assertRaised()
        ;
    }

    protected function createValidator(): NotBlankIfGatewayConfigTypeEqualsValidator
    {
        return new NotBlankIfGatewayConfigTypeEqualsValidator($this->orderRepository->reveal(), $this->cypher->reveal());
    }

    private function emptyDataProvider(): array
    {
        return [
            [null],
            [''],
        ];
    }
}
