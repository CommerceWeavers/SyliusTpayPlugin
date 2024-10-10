<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibility;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibilityValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdRequired;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdRequiredValidator;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiChannelListProviderInterface;
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
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Webmozart\Assert\InvalidArgumentException;

class TpayChannelIdEligibilityValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private const EXAMPLE_RETURN = [
        'not a bank channel' => [
            'id' => '1',
            'available' => true,
            'onlinePayment' => true,
            'instantRedirection' => false,
            'data' => 'some data'
        ],
        'unavailable channel' => [
            'id' => '2',
            'available' => false,
            'onlinePayment' => true,
            'instantRedirection' => true,
            'data' => 'some data'
        ],
        'good channel' => [
            'id' => '3',
            'available' => true,
            'onlinePayment' => true,
            'instantRedirection' => true,
            'data' => 'some data'
        ]
    ];

    private TpayApiChannelListProviderInterface|ObjectProphecy $tpayApiChannelListProvider;

    protected function setUp(): void
    {
        $this->tpayApiChannelListProvider = $this->prophesize(TpayApiChannelListProviderInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_value_is_not_an_object(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate('not_an_object', new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_a_value_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(new \stdClass(), new TpayChannelIdEligibility());
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: '11'),
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_builds_violation_if_pay_by_link_channel_id_does_not_exist(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayApiChannelListProvider->provide()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: '111'
            ),
            new TpayChannelIdEligibility(),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.does_not_exist')
            ->atPath('property.path.tpayChannelId')
            ->setCode('f2a42e4d-21e4-4728-a745-b49d1bf12138')
            ->assertRaised()
        ;
    }

    public function test_it_builds_violation_if_pay_by_link_channel_id_is_not_available(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayApiChannelListProvider->provide()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: '2'
            ),
            new TpayChannelIdEligibility(),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.available')
            ->atPath('property.path.tpayChannelId')
            ->setCode('f2a42e4d-21e4-4728-a745-b49d1bf12138')
            ->assertRaised()
        ;
    }

    public function test_it_builds_violation_if_pay_by_link_channel_id_is_not_a_bank(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayApiChannelListProvider->provide()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: '1'
            ),
            new TpayChannelIdEligibility(),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel_id.not_a_bank')
            ->atPath('property.path.tpayChannelId')
            ->setCode('f2a42e4d-21e4-4728-a745-b49d1bf12138')
            ->assertRaised()
        ;
    }

    public function test_it_does_nothing_if_pay_by_link_channel_id_is_not_provided(): void
    {
        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: null),
            new TpayChannelIdEligibility(),
        );

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_pay_by_link_channel_id_is_eligible(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->tpayApiChannelListProvider->provide()->willReturn(self::EXAMPLE_RETURN);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                tpayChannelId: '3'),
            new TpayChannelIdEligibility(),
        );

        $this->assertNoViolation();
    }

    protected function createValidator(): TpayChannelIdEligibilityValidator
    {
        return new TpayChannelIdEligibilityValidator(
            $this->tpayApiChannelListProvider->reveal(),
        );
    }
}
