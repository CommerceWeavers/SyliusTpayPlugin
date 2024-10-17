<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api;

use Payum\Core\Request\Notify as BaseNotify;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;

class NotifyTransaction extends BaseNotify
{
    public function __construct(
        $model,
        private readonly BasicPayment $basicPayment,
    ) {
        parent::__construct($model);
    }

    public function getBasicPayment(): BasicPayment
    {
        return $this->basicPayment;
    }
}
