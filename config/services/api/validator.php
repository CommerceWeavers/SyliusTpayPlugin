<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\BlikTokenRequiredValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\EncodedCardDataRequiredValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibilityValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdRequiredValidator;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_tpay.api.validator.constraint.blik_token_required_validator', BlikTokenRequiredValidator::class)
        ->args([
            service('sylius.repository.order'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_tpay.api.validator.constraint.encoded_card_data_required_validator', EncodedCardDataRequiredValidator::class)
        ->args([
            service('sylius.repository.order'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_tpay.api.validator.constraint.tpay_channel_id_required_validator', TpayChannelIdRequiredValidator::class)
        ->args([
            service('sylius.repository.order'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_tpay.api.validator.constraint.tpay_channel_id_eligibility_validator', TpayChannelIdEligibilityValidator::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.provider.tpay_api_channel_list'),
        ])
        ->tag('validator.constraint_validator')
    ;
};
