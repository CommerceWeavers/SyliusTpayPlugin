<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundDispatchEligibilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundPluginAvailabilityChecker;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Checker\RefundPluginAvailabilityCheckerInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcher;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Dispatcher\RefundDispatcherInterface;
use CommerceWeavers\SyliusTpayPlugin\Refunding\Workflow\Listener\DispatchRefundListener;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.refunding.checker.refund_plugin_availability', RefundPluginAvailabilityChecker::class)
        ->alias(RefundPluginAvailabilityCheckerInterface::class, 'commerce_weavers_sylius_tpay.refunding.checker.refund_plugin_availability')
    ;

    $services->set('commerce_weavers_sylius_tpay.refunding.checker.refund_dispatch_eligibility', RefundDispatchEligibilityChecker::class)
        ->args([
            service('commerce_weavers_sylius_tpay.refunding.checker.refund_plugin_availability'),
        ])
        ->alias(RefundDispatchEligibilityCheckerInterface::class, 'commerce_weavers_sylius_tpay.refunding.checker.refund_dispatch_eligibility')
    ;

    $services->set('commerce_weavers_sylius_tpay.refunding.dispatcher.refund', RefundDispatcher::class)
        ->public()
        ->args([
            service('payum'),
            service('commerce_weavers_sylius_tpay.refunding.checker.refund_dispatch_eligibility'),
        ])
        ->alias(RefundDispatcherInterface::class, 'commerce_weavers_sylius_tpay.refunding.dispatcher.refund')
    ;

    if (SyliusCoreBundle::VERSION_ID >= 11300) {
        $services->set('commerce_weavers_sylius_tpay.refunding.workflow.listener.dispatch_refund', DispatchRefundListener::class)
            ->args([
                service('commerce_weavers_sylius_tpay.refunding.dispatcher.refund'),
            ])
            ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.transition.refund'])
            ->tag('kernel.event_listener', ['event' => 'workflow.sylius_refund_refund_payment.transition.complete'])
        ;
    }
};
