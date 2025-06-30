<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Test\Payum;

use Payum\Core\Request\GetStatusInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;

final class SimpleGetStatusFactory implements GetStatusFactoryInterface
{
    public function createNewWithModel(mixed $model): GetStatusInterface
    {
        return new GetStatus($model);
    }
}