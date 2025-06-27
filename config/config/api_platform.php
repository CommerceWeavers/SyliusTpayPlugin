<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\OrderCannotBeFoundException;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\Exception\PaymentFailedException;
use CommerceWeavers\SyliusTpayPlugin\Api\Exception\BlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\Exception\UnresolvableNextCommandException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasExpiredException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasNotRegisteredException;
use CommerceWeavers\SyliusTpayPlugin\Payment\Exception\PaymentCannotBeCancelledException;

return function(ContainerConfigurator $configurator): void {
    $configurator->extension('api_platform', [
        'exception_to_status' => [
            OrderCannotBeFoundException::class => 404,
            PaymentCannotBeCancelledException::class => 400,
            UnresolvableNextCommandException::class => 400,
            PaymentFailedException::class => 424,
            BlikAliasAmbiguousValueException::class => 400,
            BlikAliasExpiredException::class => 400,
            BlikAliasNotRegisteredException::class => 400,
        ],
        'mapping' => [
            'paths' => [
                dirname(__DIR__) . '/api_platform',
                dirname(__DIR__) . '/resources',
            ],
        ],
    ]);
};
