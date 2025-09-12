<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\DataTransformer\ArrayFieldToNullDataTransformer;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\DataTransformer\ArrayFieldToStringDataTransformer;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.form.data_transformer.array_field_to_string', ArrayFieldToStringDataTransformer::class);
    $services->set('commerce_weavers_sylius_tpay.form.data_transformer.array_field_to_null', ArrayFieldToNullDataTransformer::class);
};
