<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Form\Type\GatewayConfigurationType;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Factory\GatewayFactory;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_channel_payment.form.type.gateway_configuration', GatewayConfigurationType::class)
        ->args([
            service('translator'),
        ])
        ->parent('commerce_weavers_sylius_tpay.form.type.abstract_tpay_gateway_configuration')
        ->tag('sylius.gateway_configuration_type', ['label' => 'commerce_weavers_sylius_tpay.admin.gateway_name.tpay_channel_pbl', 'type' => GatewayFactory::NAME])
        ->tag('form.type')
    ;
};