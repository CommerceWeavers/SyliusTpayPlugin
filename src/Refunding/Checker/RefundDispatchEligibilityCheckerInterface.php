<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Refunding\Checker;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;

interface RefundDispatchEligibilityCheckerInterface
{
    public function isEligible(PaymentInterface|RefundPaymentInterface $payment): bool;
}
