<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\PayByLinkChannelIdAvailable;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\PayByLinkChannelIdAvailableValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\PayByLinkChannelIdRequired;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\PayByLinkChannelIdRequiredValidator;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
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

class PayByLinkChannelIdAvailableValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private TpayApiBankListProviderInterface|ObjectProphecy $apiBankListProvider;

    protected function setUp(): void
    {
        $this->apiBankListProvider = $this->prophesize(TpayApiBankListProviderInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_value_is_not_an_object(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate('not_an_object', new PayByLinkChannelIdAvailable());
    }

    public function test_it_throws_an_exception_if_a_value_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(new \stdClass(), new PayByLinkChannelIdAvailable());
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                payByLinkChannelId: '11'),
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_does_nothing_if_pay_by_link_channel_id_is_not_provided(): void
    {
        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                payByLinkChannelId: null),
            new PayByLinkChannelIdAvailable(),
        );

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_pay_by_link_channel_id_is_not_available(): void
    {
        $gatewayConfig = $this->prophesize(GatewayConfigInterface::class);
        $gatewayConfig->getConfig()->willReturn(['type' => 'pay-by-link']);

        $paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig->reveal());

        $payment = $this->prophesize(PaymentInterface::class);
        $payment->getMethod()->willReturn($paymentMethod->reveal());

        $this->apiBankListProvider->provide()->willReturn([
            'channel1' => [
                'id' => '1',
                'data' => 'some data'
            ],
            'channel2' => [
                'id' => '2',
                'data' => 'some data'
            ]
        ]);

        $this->validator->validate(
            new Pay(
                'order_token',
                'https://cw.nonexisting/success',
                'https://cw.nonexisting/failure',
                payByLinkChannelId: '3'
            ),
            new PayByLinkChannelIdAvailable(),
        );

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.pay_by_link_channel.available')
            ->atPath('property.path.payByLinkChannelId')
            ->setCode('f2a42e4d-21e4-4728-a745-b49d1bf12138')
            ->assertRaised()
        ;
    }

    protected function createValidator(): PayByLinkChannelIdAvailableValidator
    {
        return new PayByLinkChannelIdAvailableValidator(
            $this->apiBankListProvider->reveal(),
        );
    }
}
