<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GetTpayTransactionsChannelsFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GetTpayTransactionsChannelsFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Payum\Factory\TpayGatewayFactoryBuilder;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_payment.payum.factory.gateway', TpayGatewayFactoryBuilder::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.cache'),
            GatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => GatewayFactory::NAME])
    ;

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_payment.payum.factory.get_tpay_transactions_channels', GetTpayTransactionsChannelsFactory::class)
        ->alias(GetTpayTransactionsChannelsFactoryInterface::class, 'commerce_weavers_sylius_tpay.pay_by_link_payment.payum.factory.get_tpay_transactions_channels')
    ;
};
