<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Twig\Component\ChannelIdPickerComponent;
use CommerceWeavers\SyliusTpayPlugin\Twig\Component\GatewayConfigurationComponent;
use Sylius\Bundle\AdminBundle\Form\Type\PaymentMethodType;
use Symfony\Component\DependencyInjection\Reference;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_channel.twig.component.gateway_configuration', GatewayConfigurationComponent::class)
        ->args([
            PaymentMethodType::class,
            GatewayFactory::NAME,
            service('form.factory'),
            service('sylius.repository.payment_method'),
            service('sylius.factory.payment_method'),
            service('commerce_weavers_sylius_tpay.tpay.cache'),
        ])
        ->call('setLiveResponder', [
            new Reference('ux.live_component.live_responder'),
        ])
        ->tag(
            'sylius.live_component.admin',
            [
                'key' => 'cw_tpay_admin:pay_by_link_channel:gateway_configuration',
                'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/gateway_configuration.html.twig',
            ],
        )
    ;

    $services->set('commerce_weavers_sylius_tpay.pay_by_link_channel.twig.component.channel_id_picker', ChannelIdPickerComponent::class)
        ->tag(
            'sylius.live_component.admin',
            [
                'key' => 'cw_tpay_admin:pay_by_link_channel:channel_id_picker',
                'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/tpay_channel_id.html.twig',
            ],
        )
    ;
};
