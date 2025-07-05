<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryCollectionExtension\CreditCardShopUserCollectionExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\CreditCardShopUserItemExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\OrderShopUserItemExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\OrderVisitorItemExtension;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider\AllowedOrderOperationsProvider;
use CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider\AllowedOrderOperationsProviderInterface;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.provider.allowed_order_operations', AllowedOrderOperationsProvider::class)
        ->alias(AllowedOrderOperationsProviderInterface::class, 'commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.provider.allowed_order_operations')
    ;

     $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.order_shop_user', OrderShopUserItemExtension::class)
         ->decorate('sylius_api.doctrine.orm.query_extension.shop.order.shop_user_based')
         ->args([
             service('.inner'),
             service(UserContextInterface::class),
             service('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.provider.allowed_order_operations'),
         ])
     ;

    $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.credit_card_shop_user', CreditCardShopUserItemExtension::class)
        ->args([
            service(UserContextInterface::class),
            service(ChannelContextInterface::class),
        ])
        ->tag('api_platform.doctrine.orm.query_extension.item')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_collection_extension.credit_card_shop_user', CreditCardShopUserCollectionExtension::class)
        ->args([
            service(UserContextInterface::class),
            service(ChannelContextInterface::class),
        ])
        ->tag('api_platform.doctrine.orm.query_extension.collection')
    ;

     $services->set('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.order_visitor', OrderVisitorItemExtension::class)
         ->decorate('sylius_api.doctrine.orm.query_extension.shop.order.visitor_based')
         ->args([
             service('.inner'),
             service(UserContextInterface::class),
             service('commerce_weavers_sylius_tpay.api.doctrine.query_item_extension.provider.allowed_order_operations'),
         ])
     ;
};
