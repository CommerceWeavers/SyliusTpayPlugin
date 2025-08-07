<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Refunding\Workflow\Listener;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcherInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Workflow\Listener\DispatchRefundListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

final class DispatchRefundListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<RefundDispatcherInterface>
     */
    private ObjectProphecy $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->prophesize(RefundDispatcherInterface::class);
    }

    public function test_it_throws_an_exception_if_the_subject_is_not_a_payment(): void
    {
        if (SyliusCoreBundle::VERSION_ID < 11300) {
            $this->markTestSkipped();
        }

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected instance of "Sylius\Component\Core\Model\PaymentInterface" or "Sylius\RefundPlugin\Entity\RefundPaymentInterface", got "stdClass"');

        $this->getTestSubject()->__invoke(new TransitionEvent(new \stdClass(), new Marking()));
    }

    public function test_it_dispatches_a_refund_request(): void
    {
        if (SyliusCoreBundle::VERSION_ID < 11300) {
            $this->markTestSkipped();
        }

        $payment = $this->prophesize(RefundPaymentInterface::class);

        $this->dispatcher->dispatch($payment->reveal())->shouldBeCalled();

        $this->getTestSubject()->__invoke(new TransitionEvent($payment->reveal(), new Marking()));
    }

    public function test_it_dispatches_a_payment_request(): void
    {
        if (SyliusCoreBundle::VERSION_ID < 11300) {
            $this->markTestSkipped();
        }

        $payment = $this->prophesize(PaymentInterface::class);

        $this->dispatcher->dispatch($payment->reveal())->shouldBeCalled();

        $this->getTestSubject()->__invoke(new TransitionEvent($payment->reveal(), new Marking()));
    }

    private function getTestSubject(): DispatchRefundListener
    {
        return new DispatchRefundListener($this->dispatcher->reveal());
    }
}
