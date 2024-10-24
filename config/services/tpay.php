<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateApplePayPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateApplePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateBlikLevelZeroPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateCardPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateGooglePayPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateInitializeApplePayPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateInitializeApplePayPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreatePayByLinkPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateRedirectBasedPaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateVisaMobilePaymentPayloadFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Factory\CreateVisaMobilePaymentPayloadFactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProvider;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\CachedTpayTransactionChannelResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Resolver\TpayTransactionChannelResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\BasicPaymentFactory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\X509Factory;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Factory\X509FactoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CachedCertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CachedTrustedCertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\CertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolver;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver\TrustedCertificateResolverInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\ChecksumVerifier;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\ChecksumVerifierInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifier;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_apple_pay_payment_payload', CreateApplePayPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateApplePayPaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_apple_pay_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_blik_level_zero_payment_payload', CreateBlikLevelZeroPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
            service('sylius.context.channel'),
        ])
        ->alias(CreateBlikLevelZeroPaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_blik_level_zero_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_card_payment_payload', CreateCardPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateCardPaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_card_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_google_pay_payment_payload', CreateGooglePayPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateGooglePayPaymentPayloadFactory::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_google_pay_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_initialize_apple_pay_payment_payload', CreateInitializeApplePayPaymentPayloadFactory::class)
        ->alias(CreateInitializeApplePayPaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_initialize_apple_pay_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_visa_mobile_payment_payload', CreateVisaMobilePaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateVisaMobilePaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_visa_mobile_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload', CreateRedirectBasedPaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.routing.generator.callback_url'),
            service('translator'),
        ])
        ->alias(CreateRedirectBasedPaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_pay_by_link_payment_payload', CreatePayByLinkPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreatePayByLinkPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_pay_by_link_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.factory.create_visa_mobile_payment_payload', CreateVisaMobilePaymentPayloadFactory::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.factory.create_redirect_based_payment_payload'),
        ])
        ->alias(CreateVisaMobilePaymentPayloadFactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.factory.create_visa_mobile_payment_payload')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.factory.basic_payment', BasicPaymentFactory::class)
        ->alias(BasicPaymentFactory::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.factory.basic_payment')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.factory.x509', X509Factory::class)
        ->alias(X509FactoryInterface::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.factory.x509')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.certificate', CertificateResolver::class)
        ->alias(CertificateResolverInterface::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.resolver.certificate')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.cached_certificate', CachedCertificateResolver::class)
        ->decorate('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.certificate')
        ->args([
            service('cache.app'),
            service('.inner'),
            param('commerce_weavers_sylius_tpay.certificate.cache_ttl_in_seconds'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.trusted_certificate', TrustedCertificateResolver::class)
        ->alias(TrustedCertificateResolverInterface::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.resolver.trusted_certificate')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.cached_trusted_certificate', CachedTrustedCertificateResolver::class)
        ->decorate('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.trusted_certificate')
        ->args([
            service('cache.app'),
            service('.inner'),
            param('commerce_weavers_sylius_tpay.certificate.cache_ttl_in_seconds'),
        ])
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum', ChecksumVerifier::class)
        ->alias(ChecksumVerifierInterface::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.verifier.checksum')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature', SignatureVerifier::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.certificate'),
            service('commerce_weavers_sylius_tpay.tpay.security.notification.resolver.trusted_certificate'),
            service('commerce_weavers_sylius_tpay.tpay.security.notification.factory.x509'),
        ])
        ->alias(SignatureVerifierInterface::class, 'commerce_weavers_sylius_tpay.tpay.security.notification.verifier.signature')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.provider.tpay_api_bank_list', TpayApiBankListProvider::class)
        ->args([
            service('commerce_weavers_sylius_tpay.tpay.resolver.tpay_transaction_channel_resolver'),
        ])
        ->alias(TpayApiBankListProviderInterface::class, 'commerce_weavers_sylius_tpay.tpay.provider.tpay_api_bank_list')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.resolver.tpay_transaction_channel_resolver', TpayTransactionChannelResolver::class)
        ->args([
            service('payum'),
        ])
        ->alias(TpayApiBankListProviderInterface::class, 'commerce_weavers_sylius_tpay.tpay.resolver.tpay_transaction_channel_resolver')
    ;

    $services->set('commerce_weavers_sylius_tpay.tpay.resolver.cached_tpay_transaction_channel_resolver', CachedTpayTransactionChannelResolver::class)
        ->decorate('commerce_weavers_sylius_tpay.tpay.resolver.tpay_transaction_channel_resolver')
        ->args([
            service('.inner'),
            service('cache.app'),
            param('commerce_weavers_sylius_tpay.tpay_transaction_channels.cache_ttl_in_seconds'),
        ])
    ;
};
