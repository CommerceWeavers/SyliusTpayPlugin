<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Refunding\Workflow\Listener;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcherInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

final class DispatchRefundListener
{
    public function __construct(
        private RefundDispatcherInterface $refundDispatcher,
    ) {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $payment = $event->getSubject();

        if (!$payment instanceof PaymentInterface) {
            throw new \UnexpectedValueException(
                sprintf('Expected instance of "%s", got "%s"', PaymentInterface::class, get_class($payment)),
            );
        }

        $this->refundDispatcher->dispatch($payment);
    }
}
