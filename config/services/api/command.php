<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\AbstractPayByHandler;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlikHandler;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByCardHandler;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByRedirectHandler;
use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayHandler;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.command.pay_handler', PayHandler::class)
        ->args([
            service('sylius.repository.order'),
            service('commerce_weavers_sylius_tpay.api.factory.next_command'),
            service('sylius.command_bus'),
        ])
        ->tag('messenger.message_handler')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.command.abstract_pay_by_handler', AbstractPayByHandler::class)
        ->abstract()
        ->args([
            service('sylius.repository.payment'),
            service('payum'),
            service('commerce_weavers_sylius_tpay.payum.factory.create_transaction'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.api.command.pay_by_blik_handler', PayByBlikHandler::class)
        ->parent('commerce_weavers_sylius_tpay.api.command.abstract_pay_by_handler')
        ->tag('messenger.message_handler')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.command.pay_by_card_handler', PayByCardHandler::class)
        ->parent('commerce_weavers_sylius_tpay.api.command.abstract_pay_by_handler')
        ->tag('messenger.message_handler')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.command.pay_by_redirect_handler', PayByRedirectHandler::class)
        ->parent('commerce_weavers_sylius_tpay.api.command.abstract_pay_by_handler')
        ->tag('messenger.message_handler')
    ;
};
