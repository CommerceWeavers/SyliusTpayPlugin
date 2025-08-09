<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\IpAddressResolver;
use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\IpAddressResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\UserAgentResolver;
use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\UserAgentResolverInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.customer.resolver.ip_address', IpAddressResolver::class)
        ->args([
            service('request_stack'),
        ])
        ->alias(IpAddressResolverInterface::class, 'commerce_weavers_sylius_tpay.customer.resolver.ip_address')
    ;

    $services->set('commerce_weavers_sylius_tpay.customer.resolver.user_agent', UserAgentResolver::class)
        ->args([
            service('request_stack'),
        ])
        ->alias(UserAgentResolverInterface::class, 'commerce_weavers_sylius_tpay.customer.resolver.user_agent')
    ;
};
