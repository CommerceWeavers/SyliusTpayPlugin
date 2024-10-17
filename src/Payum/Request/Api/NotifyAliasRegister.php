<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api;

use CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api\Notify\NotifyData;
use Payum\Core\Request\Notify as BaseNotify;

class NotifyAliasRegister extends BaseNotify
{
    public function __construct(
        $model,
        private readonly NotifyData $data,
    ) {
        parent::__construct($model);
    }

    public function getData(): NotifyData
    {
        return $this->data;
    }
}
