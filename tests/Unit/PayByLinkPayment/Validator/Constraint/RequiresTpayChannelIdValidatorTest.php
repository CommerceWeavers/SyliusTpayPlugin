<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\PayByLinkPayment\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\RequiresTpayChannelId;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Validator\Constraint\RequiresTpayChannelIdValidator;
use InvalidArgumentException;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class RequiresTpayChannelIdValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<FormInterface> */
    private ObjectProphecy $form;

    /** @var ObjectProphecy<OrderLastNewPaymentAwareInterface> */
    private ObjectProphecy $order;

    /** @var ObjectProphecy<PaymentInterface> */
    private ObjectProphecy $payment;

    /** @var ObjectProphecy<PaymentMethodInterface> */
    private ObjectProphecy $paymentMethod;

    /** @var ObjectProphecy<GatewayConfigInterface> */
    private ObjectProphecy $gatewayConfig;

    protected function setUp(): void
    {
        $this->form = $this->prophesize(FormInterface::class);
        $this->order = $this->prophesize(OrderLastNewPaymentAwareInterface::class);
        $this->payment = $this->prophesize(PaymentInterface::class);
        $this->paymentMethod = $this->prophesize(PaymentMethodInterface::class);
        $this->gatewayConfig = $this->prophesize(GatewayConfigInterface::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->validator->validate(
            ['tpay_channel_id' => null],
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_does_nothing_if_value_is_not_an_array(): void
    {
        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->validator->validate('not-an-array', new RequiresTpayChannelId());

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_factory_name_is_not_tpay_pbl(): void
    {
        $this->initializeContextWithFactoryName('tpay_card');

        $this->validator->validate([], new RequiresTpayChannelId());

        $this->assertNoViolation();
    }

    public function test_it_does_nothing_if_order_has_no_last_cart_payment(): void
    {
        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->order->getLastCartPayment()->willReturn(null);

        $this->validator->validate([], new RequiresTpayChannelId());

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_tpay_channel_id_is_missing_for_tpay_pbl(): void
    {
        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->validator->validate([], new RequiresTpayChannelId());

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel.required')
            ->atPath('property.path[tpay_channel_id]')
            ->assertRaised();
    }

    public function test_it_builds_violation_if_tpay_channel_id_is_empty_for_tpay_pbl(): void
    {
        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->validator->validate(['tpay_channel_id' => ''], new RequiresTpayChannelId());

        $this->buildViolation('commerce_weavers_sylius_tpay.shop.pay.tpay_channel.required')
            ->atPath('property.path[tpay_channel_id]')
            ->assertRaised();
    }

    public function test_it_does_nothing_if_tpay_channel_id_is_provided_for_tpay_pbl(): void
    {
        $this->initializeContextWithFactoryName('tpay_pbl');

        $this->validator->validate(['tpay_channel_id' => '123'], new RequiresTpayChannelId());

        $this->assertNoViolation();
    }

    protected function createValidator(): RequiresTpayChannelIdValidator
    {
        return new RequiresTpayChannelIdValidator();
    }

    private function initializeContextWithFactoryName(?string $factoryName): void
    {
        $this->gatewayConfig->getFactoryName()->willReturn($factoryName);
        $this->paymentMethod->getGatewayConfig()->willReturn($this->gatewayConfig->reveal());
        $this->payment->getMethod()->willReturn($this->paymentMethod->reveal());
        $this->order->getLastCartPayment()->willReturn($this->payment->reveal());

        $this->form->getData()->willReturn($this->order->reveal());
        $this->setRoot($this->form->reveal());
    }
}
