<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use Sylius\Component\Core\Model\PaymentInterface;

interface OrderLastNewPaymentAwareInterface
{
    public function getLastCartPayment(): ?PaymentInterface;

    public function getLastNewPayment(): ?PaymentInterface;
}
