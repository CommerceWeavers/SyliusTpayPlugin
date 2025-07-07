<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Checker;

use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityCheckerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellationPossibilityCheckerTest extends TestCase
{
    use ProphecyTrait;


    public function test_it_returns_if_a__payment_using_new_state_machine_if_present(): void
    {
        if (!class_exists(StateMachineInterface::class)) {
            $this->markTestSkipped('This test requires Sylius 1.13');
        }

        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $stateMachine->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(true);

        $this->createTestSubject($stateMachine->reveal())->canBeCancelled($payment->reveal());
    }

    public function test_it_fallbacks_to_the_winzou_state_machine_while_checking_if_a_payment_can_be_cancelled(): void
    {
        if (!interface_exists('SM\Factory\FactoryInterface')) {
            $this->markTestSkipped('This test requires winzou/state-machine-bundle');
        }

        $payment = $this->prophesize(PaymentInterface::class);

        $winzouStateMachine = $this->prophesize('SM\StateMachine\StateMachineInterface');
        $winzouStateMachine->can(PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(true);

        $stateMachineFactory = $this->prophesize('SM\Factory\FactoryInterface');
        $stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->willReturn($winzouStateMachine);

        $canceller = new PaymentCancellationPossibilityChecker(null, $stateMachineFactory->reveal());

        $canceller->canBeCancelled($payment->reveal());
    }

    private function createTestSubject(?StateMachineInterface $stateMachine = null): PaymentCancellationPossibilityCheckerInterface
    {
        return new PaymentCancellationPossibilityChecker($stateMachine, null);
    }
}
