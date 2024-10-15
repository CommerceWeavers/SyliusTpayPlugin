<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibilityValidator;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiChannelListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolverInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Webmozart\Assert\InvalidArgumentException;

class TpayChannelIdEligibilityValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private const EXAMPLE_RETURN = [
        //
        '1' => [
            'id' => '1',
            'name' => 'not a bank channel',
            'available' => true,
            'onlinePayment' => true,
            'instantRedirection' => false,
            'data' => 'some data'
        ],
        '2' => [
            'id' => '2',
            'name' => 'unavailable channel',
            'available' => false,
            'onlinePayment' => true,
            'instantRedirection' => true,
            'data' => 'some data'
        ],
        '3' => [
            'id' => '3',
            'name' => 'good channel',
            'available' => true,
            'onlinePayment' => true,
            'instantRedirection' => true,
            'data' => 'some data'
        ]
    ];

    private TpayTransactionChannelResolverInterface|ObjectProphecy $tpayTransactionChannelResolver;

    protected function setUp(): void
    {
        $this->tpayTransactionChannelResolver = $this->prophesize(TpayTransactionChannelResolverInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_value_is_not_a_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(new \stdClass(), new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_a_value_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(1, new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(
            '11',
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_builds_violation_if_tpay_channel_id_does_not_exist(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayTransactionChannelResolver->resolve()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate(
            '111',
            new TpayChannelIdEligibility(),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.does_not_exist')
            ->setCode('f51e4f12-121a-4a1f-9cac-9862cf54732f')
            ->assertRaised()
        ;
    }

    public function test_it_builds_violation_if_tpay_channel_id_is_not_available(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayTransactionChannelResolver->resolve()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate('2', new TpayChannelIdEligibility());

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_available')
            ->setCode('f2a42e4d-21e4-4728-a745-b49d1bf12138')
            ->assertRaised()
        ;
    }

    public function test_it_builds_violation_if_tpay_channel_id_is_not_a_bank(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayTransactionChannelResolver->resolve()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate('1', new TpayChannelIdEligibility());

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_a_bank')
            ->setCode('2ecd8f05-0500-489e-93ca-701167e07768')
            ->assertRaised()
        ;
    }

    public function test_it_does_nothing_if_tpay_channel_id_is_not_provided(): void
    {
        $this->validator->validate(null, new TpayChannelIdEligibility());

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_tpay_channel_id_is_eligible(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayTransactionChannelResolver->resolve()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate('3', new TpayChannelIdEligibility());

        $this->assertNoViolation();
    }

    protected function createValidator(): TpayChannelIdEligibilityValidator
    {
        return new TpayChannelIdEligibilityValidator(
            $this->tpayTransactionChannelResolver->reveal(),
        );
    }
}
