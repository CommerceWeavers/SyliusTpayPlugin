<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Action\CreatePayByLinkChannelTransactionAction;
use CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Payum\Factory\GatewayFactory;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->public()
    ;

    $services->set(CreatePayByLinkChannelTransactionAction::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_pay_by_link_channel_payment_payload'),
            service('commerce_weavers_sylius_tpay.payum.factory.token.notify'),
        ])
        ->tag('payum.action', ['factory' => GatewayFactory::NAME, 'alias' => 'cw.tpay_pbl.create_pay_by_link_channel_transaction'])
    ;
};
