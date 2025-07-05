<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payment\Canceller;

use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;
use Sylius\Abstraction\StateMachine\Exception\StateMachineExecutionException;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCanceller implements PaymentCancellerInterface
{
    public function __construct(
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    public function cancel(PaymentInterface $payment): void
    {
        try {
            $this->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);
        } catch (StateMachineExecutionException) {
            throw new PaymentCannotBeCancelledException($payment);
        }
    }

    private function apply(
        PaymentInterface $payment,
        string $graph,
        string $transition,
    ): void {
        $this->stateMachine->apply($payment, $graph, $transition);
    }
}
