<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfBlikAliasActionIsRegisterValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayConfigTypeEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\NotBlankIfGatewayNameEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\OneOfPropertiesRequiredIfGatewayNameEqualsValidator;
use CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint\TpayChannelIdEligibilityValidator;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.not_blank_if_blik_alias_action_is_register', NotBlankIfBlikAliasActionIsRegisterValidator::class)
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.not_blank_if_gateway_config_type_equals', NotBlankIfGatewayConfigTypeEqualsValidator::class)
        ->args([
            service('sylius.repository.order'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.not_blank_if_gateway_name_equals', NotBlankIfGatewayNameEqualsValidator::class)
        ->args([
            service('sylius.repository.order'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.one_of_properties_required_if_gateway_config_type_equals', OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator::class)
        ->args([
            service('sylius.repository.order'),
            service('payum.dynamic_gateways.cypher'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.one_of_properties_required_if_gateway_name_equals', OneOfPropertiesRequiredIfGatewayNameEqualsValidator::class)
        ->args([
            service('sylius.repository.order'),
        ])
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.api.validator.constraint.tpay_channel_id_eligibility_validator', TpayChannelIdEligibilityValidator::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.resolver.cached_tpay_transaction_channel_resolver'),
        ])
        ->tag('validator.constraint_validator')
    ;
};
