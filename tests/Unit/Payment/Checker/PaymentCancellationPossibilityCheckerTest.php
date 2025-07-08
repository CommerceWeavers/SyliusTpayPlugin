<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Checker;

use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Payment\Checker\PaymentCancellationPossibilityCheckerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellationPossibilityCheckerTest extends TestCase
{
    use ProphecyTrait;


    public function test_it_returns_if_a_payment_can_be_cancelled(): void
    {
        $stateMachine = $this->prophesize(StateMachineInterface::class);
        $payment = $this->prophesize(PaymentInterface::class);

        $stateMachine->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(true);

        $this->createTestSubject($stateMachine->reveal())->canBeCancelled($payment->reveal());

        $stateMachine->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled()->willReturn(false);

        $this->createTestSubject($stateMachine->reveal())->canBeCancelled($payment->reveal());
    }

    private function createTestSubject(?StateMachineInterface $stateMachine = null): PaymentCancellationPossibilityCheckerInterface
    {
        return new PaymentCancellationPossibilityChecker($stateMachine);
    }
}
