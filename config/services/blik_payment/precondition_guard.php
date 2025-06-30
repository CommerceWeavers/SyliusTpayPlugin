<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\ActiveBlikAliasPreconditionGuard;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\ActiveBlikAliasPreconditionGuardInterface;
use Symfony\Component\Clock\ClockInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.blik_payment.precondition_guard.active_blik_alias', ActiveBlikAliasPreconditionGuard::class)
        ->args([
            service(ClockInterface::class),
        ])
        ->alias(ActiveBlikAliasPreconditionGuardInterface::class, 'commerce_weavers_sylius_tpay.blik_payment.precondition_guard.active_blik_alias')
    ;
};
