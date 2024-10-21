<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\OrderCannotBeFoundException;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnresolvableNextCommandException;
use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;

return function(ContainerConfigurator $configurator): void {
    $configurator->extension('api_platform', [
        'exception_to_status' => [
            OrderCannotBeFoundException::class => 404,
            PaymentCannotBeCancelledException::class => 400,
            UnresolvableNextCommandException::class => 400,
        ],
        'mapping' => [
            'paths' => [
                dirname(__DIR__) . '/api_resources',
            ],
        ],
    ]);
};
