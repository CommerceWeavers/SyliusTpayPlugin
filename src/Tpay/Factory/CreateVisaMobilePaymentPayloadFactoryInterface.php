<?php

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use Sylius\Component\Core\Model\PaymentInterface;

interface CreateVisaMobilePaymentPayloadFactoryInterface
{
    public function createFrom(PaymentInterface $payment, string $notifyUrl, string $localeCode): array;
}
