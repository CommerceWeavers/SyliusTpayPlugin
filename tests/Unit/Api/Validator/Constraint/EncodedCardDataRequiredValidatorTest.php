<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\EncodedCardDataRequired;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\EncodedCardDataRequiredValidator;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class EncodedCardDataRequiredValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private OrderRepositoryInterface|ObjectProphecy $orderRepository;

    private CypherInterface|ObjectProphecy $cypher;

    protected function setUp(): void
    {
        $this->orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $this->cypher = $this->prophesize(CypherInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_value_is_not_an_object(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate('not_an_object', new EncodedCardDataRequired());
    }

    public function test_it_throws_an_exception_if_a_value_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new \stdClass(), new EncodedCardDataRequired());
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(
            new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure', '777123'),
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_does_not_validate_if_encoded_card_data_is_provided(): void
    {
        $this->validator->validate(
            new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure', encodedCardData: 'encoded_card_data'),
            new EncodedCardDataRequired(),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_order_does_not_exist(): void
    {
        $this->orderRepository->findOneByTokenValue('order_token')->willReturn(null);

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_payment_does_not_exist(): void
    {
        $order = $this->prophesize(OrderInterface::class);

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_payment_method_does_not_exist(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_gateway_config_does_not_exist(): void
    {
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->assertNoViolation();
    }

    public function test_it_decrypts_gateway_config_if_it_is_encrypted(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $gatewayConfig->decrypt($this->cypher->reveal())->shouldBeCalled();
        $gatewayConfig->getConfig()->willReturn([]);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());
    }

    public function test_it_does_not_validate_if_gateway_config_type_is_not_card(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'not_card']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_encoded_card_data_is_not_provided(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'card']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $order = $this->prophesize(OrderInterface::class);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());

        $this->orderRepository->findOneByTokenValue('order_token')->willReturn($order->reveal());

        $this->validator->validate(new Pay('order_token', 'https://cw.nonexisting/success', 'https://cw.nonexisting/failure'), new EncodedCardDataRequired());

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.encoded_card_data.required')
            ->atPath('property.path.encodedCardData')
            ->setCode('15b746fa-1620-4325-ada6-e1c0c5e574b3')
            ->assertRaised()
        ;
    }

    protected function createValidator(): EncodedCardDataRequiredValidator
    {
        return new EncodedCardDataRequiredValidator($this->orderRepository->reveal(), $this->cypher->reveal());
    }
}
