<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory;

use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;

interface BasicPaymentFactoryInterface
{
    public function createFromArray(array $data): BasicPayment;
}
