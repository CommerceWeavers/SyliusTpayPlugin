<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcher;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcherInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Workflow\Listener\DispatchRefundListener;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.refunding.dispatcher.refund', RefundDispatcher::class)
        ->public()
        ->args([
            service('payum'),
        ])
        ->alias(RefundDispatcherInterface::class, 'commerce_weavers_sylius_tpay.refunding.dispatcher.refund')
    ;

    if (SyliusCoreBundle::VERSION_ID >= 11300) {
        $services->set('commerce_weavers_sylius_tpay.refunding.workflow.listener.dispatch_refund', DispatchRefundListener::class)
            ->args([
                service('commerce_weavers_sylius_tpay.refunding.dispatcher.refund'),
            ])
            ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.transition.refund'])
        ;
    }
};
