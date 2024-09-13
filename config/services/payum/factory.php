<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\CreateTransactionFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\NotifyFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\NotifyFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactory;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\Token\NotifyTokenFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactory;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.payum.factory.tpay_gateway', GatewayFactoryBuilder::class)
        ->args([
            TpayGatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => TpayGatewayFactory::NAME])
    ;

    $services->set('commerce_weavers.tpay.payum.factory.notify', NotifyFactory::class)
        ->alias(NotifyFactoryInterface::class, 'commerce_weavers.tpay.payum.factory.notify')
    ;

    $services->set('commerce_weavers.tpay.payum.factory.create_transaction', CreateTransactionFactory::class)
        ->alias(CreateTransactionFactoryInterface::class, 'commerce_weavers.tpay.payum.factory.create_transaction')
    ;

    $services->set('commerce_weavers_tpay.payum.factory.token.notify', NotifyTokenFactory::class)
        ->args([
            service('payum'),
            service('router'),
            param('commerce_weavers_tpay.payum.create_transaction.notify_route'),
        ])
        ->alias(NotifyTokenFactoryInterface::class, 'commerce_weavers_tpay.payum.factory.token.notify')
    ;
};
