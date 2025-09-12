<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Test\Payum\Cypher\FakeCypher;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\AlwaysValidChecksumVerifier;
use Tests\CommerceWeavers\SyliusTpayPlugin\Helper\AlwaysValidSignatureVerifier;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('payum.dynamic_gateways.cypher', FakeCypher::class)
        ->args([
            env('PAYUM_CYPHER_KEY'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.tests.always_valid_signature_verifier', AlwaysValidSignatureVerifier::class);

    $services->set('commerce_weavers_sylius_tpay.tests.always_valid_checksum_verifier', AlwaysValidChecksumVerifier::class);

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature')
        ->alias('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature', 'commerce_weavers_sylius_tpay.tests.always_valid_signature_verifier');

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum')
        ->alias('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum', 'commerce_weavers_sylius_tpay.tests.always_valid_checksum_verifier');
};
