<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payment\Canceller;

use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;
use SM\Factory\FactoryInterface;
use Sylius\Abstraction\StateMachine\Exception\StateMachineExecutionException;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCanceller implements PaymentCancellerInterface
{
    public function __construct(
        private readonly ?StateMachineInterface $stateMachine,
        private readonly ?FactoryInterface $stateMachineFactory,
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
        if (null !== $this->stateMachine) {
            $this->stateMachine->apply($payment, $graph, $transition);

            return;
        }

        if (null !== $this->stateMachineFactory) {
            $this->stateMachineFactory->get($payment, $graph)->apply($transition);
        }
    }
}
