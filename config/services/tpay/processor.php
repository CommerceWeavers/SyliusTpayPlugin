<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessor;
use CommerceWeavers\SyliusTpayPlugin\Payum\Processor\CreateTransactionProcessorInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.tpay.processor.create_transaction', CreateTransactionProcessor::class)
        ->args([
            service('payum'),
            service('commerce_weavers_sylius_tpay.payum.factory.create_transaction'),
            service(GetStatusFactoryInterface::class),
            service('translator'),
        ])
        ->alias(CreateTransactionProcessorInterface::class, 'commerce_weavers_sylius_tpay.tpay.processor.create_transaction')
    ;
};
