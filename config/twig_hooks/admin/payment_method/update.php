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
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_redirect.config' => [
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
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_pbl.config' => [
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
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_blik.config' => [
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
            ],
            'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.tpay_pbl_channel.config' => [
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
                'tpay_channel_id' => [
                    'component' => 'cw_tpay_admin:pay_by_link_channel:channel_id_picker',
                    'priority' => 192,
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
        ],
    ]);
};
