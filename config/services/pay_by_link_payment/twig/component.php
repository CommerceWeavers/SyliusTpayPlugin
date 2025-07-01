<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Payum\Factory\GatewayFactory;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Twig\Component\ChannelPickerComponent;
use CommerceWeavers\SyliusTpayPlugin\Twig\Component\GatewayConfigurationComponent;
use Sylius\Bundle\AdminBundle\Form\Type\PaymentMethodType;
use Symfony\Component\DependencyInjection\Reference;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.pay_by_link.twig.component.gateway_configuration', GatewayConfigurationComponent::class)
        ->args([
            PaymentMethodType::class,
            GatewayFactory::NAME,
            service('form.factory'),
            service('sylius.repository.payment_method'),
            service('sylius.factory.payment_method'),
        ])
        ->call('setLiveResponder', [
            new Reference('ux.live_component.live_responder'),
        ])
        ->tag(
            'sylius.live_component.admin',
            [
                'key' => 'cw_tpay_admin:pay_by_link:gateway_configuration',
                'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/gateway_configuration.html.twig',
            ],
        )
    ;

    $services->set('commerce_weavers_sylius_tpay.pay_by_link.twig.component.channel_picker', ChannelPickerComponent::class)
        ->args([
            service('form.factory'),
            service('sylius.repository.order'),
            service('commerce_weavers_sylius_tpay.tpay.provider.order_aware_validated_tpay_api_bank_list'),
        ])
        ->call('setLiveResponder', [
            new Reference('ux.live_component.live_responder'),
        ])
        ->tag(
            'sylius.live_component.shop',
            [
                'key' => 'cw_tpay_shop:pay_by_link:channel_picker',
                'template' => '@CommerceWeaversSyliusTpayPlugin/shop/payment/pay_by_link.html.twig',
            ],
        )
    ;
};
