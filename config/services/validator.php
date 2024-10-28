<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\EncodedGooglePayTokenValidator;
use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\ForAuthorizedUserOnlyValidator;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.validator.constraint.encoded_google_pay_token', EncodedGooglePayTokenValidator::class)
        ->tag('validator.constraint_validator')
    ;

    $services->set('commerce_weavers_sylius_tpay.validator.constraint.for_authorized_user_only', ForAuthorizedUserOnlyValidator::class)
        ->args([
            service('security.helper'),
        ])
        ->tag('validator.constraint_validator')
    ;
};