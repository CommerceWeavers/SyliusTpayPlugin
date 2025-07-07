<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payment\Checker;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellationPossibilityChecker implements PaymentCancellationPossibilityCheckerInterface
{
    public function __construct(
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    public function canBeCancelled(PaymentInterface $payment): bool
    {
        return $this->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);
    }

    private function can(
        PaymentInterface $payment,
        string $graph,
        string $transition,
    ): bool {
        return $this->stateMachine->can($payment, $graph, $transition);
    }
}
