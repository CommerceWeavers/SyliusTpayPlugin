<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByBlikFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByCardFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommand\PayByRedirectFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactory;
use CommerceWeavers\SyliusTpayPlugin\Api\Factory\NextCommandFactoryInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.factory.next_command', NextCommandFactory::class)
        ->args([
            tagged_iterator('commerce_weavers_sylius_tpay.api.factory.next_command'),
        ])
        ->alias(NextCommandFactoryInterface::class, 'commerce_weavers_sylius_tpay.api.factory.next_command')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.factory.next_command.pay_by_blik', PayByBlikFactory::class)
        ->tag('commerce_weavers_sylius_tpay.api.factory.next_command')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.factory.next_command.pay_by_card', PayByCardFactory::class)
        ->tag('commerce_weavers_sylius_tpay.api.factory.next_command')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.factory.next_command.pay_by_redirect', PayByRedirectFactory::class)
        ->args([
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('commerce_weavers_sylius_tpay.api.factory.next_command')
    ;
};
