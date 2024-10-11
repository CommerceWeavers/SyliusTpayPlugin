<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Checker;

use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityCheckerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface as WinzouStateMachineInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellationPossibilityCheckerTest extends TestCase
{
    use ProphecyTrait;

    private StateMachineInterface|ObjectProphecy $stateMachine;

    private FactoryInterface|ObjectProphecy $stateMachineFactory;

    protected function setUp(): void
    {
        $this->stateMachine = $this->prophesize(StateMachineInterface::class);
        $this->stateMachineFactory = $this->prophesize(FactoryInterface::class);
    }

    public function test_it_returns_if_a__payment_using_new_state_machine_if_present(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $this->stateMachine->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(true);
        $this->stateMachineFactory->get(Argument::cetera())->shouldNotBeCalled();

        $this->createTestSubject()->canBeCancelled($payment->reveal());
    }

    public function test_it_fallbacks_to_the_winzou_state_machine_while_checking_if_a_payment_can_be_cancelled(): void
    {
        $payment = $this->prophesize(PaymentInterface::class);

        $winzouStateMachine = $this->prophesize(WinzouStateMachineInterface::class);
        $winzouStateMachine->can(PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(true);

        $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->willReturn($winzouStateMachine);

        $canceller = new PaymentCancellationPossibilityChecker(null, $this->stateMachineFactory->reveal());

        $canceller->canBeCancelled($payment->reveal());
    }

    private function createTestSubject(): PaymentCancellationPossibilityCheckerInterface
    {
        return new PaymentCancellationPossibilityChecker($this->stateMachine->reveal(), $this->stateMachineFactory->reveal());
    }
}
