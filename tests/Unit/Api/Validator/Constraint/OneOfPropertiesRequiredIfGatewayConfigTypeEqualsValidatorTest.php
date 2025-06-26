<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Enum\BlikAliasAction;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEquals;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayConfigTypeEquals;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator;
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
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidatorTest extends ConstraintValidatorTestCase
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

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            $this->getPayCommand(),
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_throws_an_exception_if_value_is_not_a_pay_command(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            new \stdClass(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );
    }

    public function test_it_does_not_validate_if_order_does_not_exist(): void
    {
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn(null);

        $this->validator->validate(
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_payment_does_not_exist(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order);
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn(null);

        $this->validator->validate(
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
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
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
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
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
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
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
    }

    public function test_it_does_not_validate_if_gateway_config_type_is_different(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn(['type' => 'google_pay']);

        $this->validator->validate(
            $this->getPayCommand(),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(paymentMethodType: 'blik'),
        );

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this->assertNoViolation();
    }

    public function test_it_does_not_validate_if_at_least_one_property_is_not_blank(): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn(['type' => 'blik']);

        $this->validator->validate(
            $this->getPayCommand(blikAliasAction: BlikAliasAction::APPLY),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(
                paymentMethodType: 'blik',
                properties: ['blikToken', 'blikAliasAction'],
            ),
        );

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this->assertNoViolation();
    }

    /** @dataProvider emptyDataProvider */
    public function test_it_builds_violation_if_all_properties_are_blank(mixed $value): void
    {
        $order = $this->prophesize(OrderInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);
        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->willImplement(CryptedInterface::class);
        $this->orderRepository->findOneByTokenValue('orderToken123')->willReturn($order->reveal());
        $order->getLastPayment(PaymentInterface::STATE_NEW)->willReturn($payment->reveal());
        $payment->getMethod()->willReturn($paymentMethod->reveal());
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());
        $gatewayConfig->getConfig()->willReturn(['type' => 'blik']);

        $this->validator->validate(
            $this->getPayCommand(blikToken: $value),
            new OneOfPropertiesRequiredIfGatewayConfigTypeEquals(
                paymentMethodType: 'blik',
                properties: ['blikToken', 'blikAliasAction'],
            ),
        );

        $gatewayConfig->decrypt($this->cypher)->shouldBeCalled();
        $this
            ->buildViolation('commerce_weavers_sylius_tpay.shop.pay.fields_required')
            ->setCode(OneOfPropertiesRequiredIfGatewayConfigTypeEquals::ALL_FIELDS_ARE_BLANK_ERROR)
            ->assertRaised()
        ;
    }

    protected function createValidator(): OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator
    {
        return new OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator($this->orderRepository->reveal(), $this->cypher->reveal());
    }

    private function getPayCommand(?string $blikToken = null, ?BlikAliasAction $blikAliasAction = null): Pay
    {
        return new Pay(
            'orderToken123',
            'http://example.com?success',
            'http://example.com?failure',
            blikToken: $blikToken,
            blikAliasAction: $blikAliasAction,
        );
    }

    private function emptyDataProvider(): array
    {
        return [
            [null],
            [''],
        ];
    }
}
