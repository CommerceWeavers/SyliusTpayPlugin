<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_admin.payment_method.update#javascripts' => [
                'payment_method' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/scripts/payment_method.html.twig',
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_redirect' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:redirect_payment:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_pbl' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:pay_by_link:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_pbl_channel' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:pay_by_link_channel:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_blik' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:redirect_payment:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_google_pay' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:google_pay:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_apple_pay' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:apple_pay:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'commerce_weavers_sylius_tpay_admin.payment_method.form.sections.gateway_configuration.config' => [
                'client_id' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/client_id.html.twig',
                    'priority' => 1024,
                ],
                'client_secret' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/client_secret.html.twig',
                    'priority' => 512,
                ],
                'merchant_id' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/merchant_id.html.twig',
                    'priority' => 256,
                ],
                'notification_security_code' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/notification_security_code.html.twig',
                    'priority' => 128,
                ],
                'production_mode' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/production_mode.html.twig',
                    'priority' => 64,
                ],
                'test_connection' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/shared/gateway_configuration/test_connection.html.twig',
                    'priority' => 32,
                ],
            ],
            'commerce_weavers_sylius_tpay_admin.payment_method.form.sections.gateway_configuration.config.tpay_pbl_channel' => [
                'tpay_channel_id' => [
                    'component' => 'cw_tpay_admin:pay_by_link_channel:channel_id_picker',
                    'priority' => 192,
                ],
            ],
            'commerce_weavers_sylius_tpay_admin.payment_method.form.sections.gateway_configuration.config.tpay_google_pay' => [
                'google_merchant_id' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/google_pay/gateway_configuration/merchant_id.html.twig',
                    'priority' => 192,
                ],
            ],
            'commerce_weavers_sylius_tpay_admin.payment_method.form.sections.gateway_configuration.config.tpay_apple_pay' => [
                'apple_merchant_id' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/apple_pay/gateway_configuration/merchant_id.html.twig',
                    'priority' => 192,
                ],
            ],
        ],
    ]);
};
