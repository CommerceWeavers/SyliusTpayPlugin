<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Checker;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

interface PaymentMethodSupportedForOrderCheckerInterface
{
    public function isSupportedForOrder(PaymentMethodInterface $paymentMethod, OrderInterface $order): bool;
}
