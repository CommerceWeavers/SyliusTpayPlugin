<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\OrderShopUserItemExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\OrderVisitorItemExtension;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.order_shop_user', OrderShopUserItemExtension::class)
        ->decorate(\Sylius\Bundle\ApiBundle\Doctrine\QueryItemExtension\OrderShopUserItemExtension::class)
        ->args([
            service('.inner'),
            service(UserContextInterface::class),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.order_visitor', OrderVisitorItemExtension::class)
        ->decorate(\Sylius\Bundle\ApiBundle\Doctrine\QueryItemExtension\OrderVisitorItemExtension::class)
        ->args([
            service('.inner'),
            service(UserContextInterface::class),
        ])
    ;
};
