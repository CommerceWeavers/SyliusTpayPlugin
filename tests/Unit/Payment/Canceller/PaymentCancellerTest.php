<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Canceller;

use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCanceller;
use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCancellerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellerTest extends TestCase
{
    use ProphecyTrait;


    public function test_it_cancels_a_payment_using_the_new_state_machine_if_present(): void
    {
        if (!class_exists(StateMachineInterface::class)) {
            $this->markTestSkipped('This test requires Sylius 1.13');
        }

        $stateMachine = $this->prophesize(StateMachineInterface::class);

        $payment = $this->prophesize(PaymentInterface::class);

        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled();

        $this->createTestSubject($stateMachine->reveal())->cancel($payment->reveal());
    }

    public function test_it_fallbacks_to_the_winzou_state_machine_while_cancelling_a_payment(): void
    {
        if (!interface_exists('SM\Factory\FactoryInterface')) {
            $this->markTestSkipped('This test requires winzou/state-machine-bundle');
        }

        $payment = $this->prophesize(PaymentInterface::class);

        $winzouStateMachine = $this->prophesize('SM\StateMachine\StateMachineInterface');
        $winzouStateMachine->apply(PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled();

        $stateMachineFactory = $this->prophesize('SM\Factory\FactoryInterface');
        $stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->willReturn($winzouStateMachine);

        $canceller = new PaymentCanceller(null, $stateMachineFactory->reveal());

        $canceller->cancel($payment->reveal());
    }

    private function createTestSubject(?StateMachineInterface $stateMachine = null): PaymentCancellerInterface
    {
        return new PaymentCanceller($stateMachine, null);
    }
}
