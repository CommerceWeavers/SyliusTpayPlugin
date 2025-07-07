<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Payment\Canceller;

use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCanceller;
use CommerceWeavers\SyliusTpayPlugin\Payment\Canceller\PaymentCancellerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class PaymentCancellerTest extends TestCase
{
    use ProphecyTrait;


    public function test_it_cancels_a_payment(): void
    {
        $stateMachine = $this->prophesize(StateMachineInterface::class);

        $payment = $this->prophesize(PaymentInterface::class);

        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled();

        $this->createTestSubject($stateMachine->reveal())->cancel($payment->reveal());
    }

    private function createTestSubject(?StateMachineInterface $stateMachine = null): PaymentCancellerInterface
    {
        return new PaymentCanceller($stateMachine);
    }
}
