<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\EventListener;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\RemoveUnnecessaryPaymentDetailsFieldsListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

final class RemoveUnnecessaryPaymentDetailsFieldsListenerTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_leaves_blik_field_once_blik_token_is_set(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldBeCalled()->willReturn($form);
        $form->remove('blik_token')->shouldNotBeCalled();
        $form->remove('google_pay_token')->shouldBeCalled()->willReturn($form);
        $form->remove('tpay_channel_id')->shouldBeCalled()->willReturn($form);
        $form->remove('visa_mobile_phone_number')->shouldBeCalled()->willReturn($form);

        $event = new FormEvent($form->reveal(), ['blik_token' => '123456']);

        $this->createTestSubject()->__invoke($event);
    }

    public function test_it_leaves_google_pay_token_field_once_google_pay_token_is_set(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldBeCalled()->willReturn($form);
        $form->remove('blik_token')->shouldBeCalled()->willReturn($form);
        $form->remove('google_pay_token')->shouldNotBeCalled();
        $form->remove('tpay_channel_id')->shouldBeCalled()->willReturn($form);

        $event = new FormEvent($form->reveal(), ['google_pay_token' => '123456']);

        $this->createTestSubject()->__invoke($event);
    }

    public function test_it_leaves_card_field_once_card_is_set(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldNotBeCalled();
        $form->remove('blik_token')->shouldBeCalled()->willReturn($form);
        $form->remove('google_pay_token')->shouldBeCalled()->willReturn($form);
        $form->remove('tpay_channel_id')->shouldBeCalled()->willReturn($form);
        $form->remove('visa_mobile_phone_number')->shouldBeCalled()->willReturn($form);

        $event = new FormEvent($form->reveal(), ['card' => 'h45h']);

        $this->createTestSubject()->__invoke($event);
    }

    public function test_it_leaves_pbl_channel_id_field_once_pbl_channel_id_is_set(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldBeCalled()->willReturn($form);
        $form->remove('blik_token')->shouldBeCalled()->willReturn($form);
        $form->remove('google_pay_token')->shouldBeCalled()->willReturn($form);
        $form->remove('tpay_channel_id')->shouldNotBeCalled();
        $form->remove('visa_mobile_phone_number')->shouldBeCalled()->willReturn($form);

        $event = new FormEvent($form->reveal(), ['tpay_channel_id' => 1]);

        $this->createTestSubject()->__invoke($event);
    }

    public function test_it_leaves_visa_mobile_field_once_visa_mobile_is_set(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldBeCalled()->willReturn($form);
        $form->remove('blik_token')->shouldBeCalled()->willReturn($form);
        $form->remove('tpay_channel_id')->shouldBeCalled()->willReturn($form);
        $form->remove('visa_mobile_phone_number')->shouldNotBeCalled();

        $event = new FormEvent($form->reveal(), ['visa_mobile_phone_number' => true]);

        $this->createTestSubject()->__invoke($event);
    }

    public function test_it_removes_all_additional_fields_if_none_of_them_are_passed(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $form->remove('card')->shouldBeCalled()->willReturn($form);
        $form->remove('blik_token')->shouldBeCalled()->willReturn($form);
        $form->remove('google_pay_token')->shouldBeCalled()->willReturn($form);
        $form->remove('tpay_channel_id')->shouldBeCalled()->willReturn($form);
        $form->remove('visa_mobile_phone_number')->shouldBeCalled()->willReturn($form);

        $event = new FormEvent($form->reveal(), []);

        $this->createTestSubject()->__invoke($event);
    }

    public function createTestSubject(): object
    {
        return new RemoveUnnecessaryPaymentDetailsFieldsListener();
    }
}
