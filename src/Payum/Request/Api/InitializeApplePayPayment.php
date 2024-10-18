<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Request\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Generic;

class InitializeApplePayPayment extends Generic
{
    public function __construct(ArrayObject $model, private readonly ArrayObject $output)
    {
        parent::__construct($model);
    }

    public function getOutput(): ArrayObject
    {
        return $this->output;
    }
}
