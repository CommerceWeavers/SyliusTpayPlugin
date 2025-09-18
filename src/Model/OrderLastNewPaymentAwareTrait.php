<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use Sylius\Component\Core\Model\PaymentInterface;

trait OrderLastNewPaymentAwareTrait
{
    public function getLastCartPayment(): ?PaymentInterface
    {
        return $this->getLastPayment('cart');
    }

    public function getLastNewPayment(): ?PaymentInterface
    {
        return $this->getLastPayment('new');
    }
}
