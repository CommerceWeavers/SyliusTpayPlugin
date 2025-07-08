<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Form\Type\GatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Payum\Factory\GatewayFactory;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.blik_payment.form.type.gateway_configuration', GatewayConfigurationType::class)
        ->parent('commerce_weavers_sylius_tpay.form.type.abstract_tpay_gateway_configuration')
        ->tag('sylius.gateway_configuration_type', ['label' => 'commerce_weavers_sylius_tpay.admin.gateway_name.tpay_blik', 'type' => GatewayFactory::NAME])
        ->tag('form.type')
    ;
};
