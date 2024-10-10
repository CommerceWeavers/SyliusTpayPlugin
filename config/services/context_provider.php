<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\ContextProvider\BankListContextProvider;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(BankListContextProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.provider.tpay_api_bank_list'),
        ])
        ->tag('sylius.ui.template_event.context_provider')
    ;
};
