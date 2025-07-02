<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_admin.payment_method.create#javascripts' => [
                'payment_method' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/admin/scripts/payment_method.html.twig',
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_redirect' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:redirect_payment:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_pbl' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:pay_by_link:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_pbl_channel' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:pay_by_link_channel:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_blik' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:redirect_payment:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_google_pay' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:google_pay:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_apple_pay' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:apple_pay:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
            'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.tpay_visa_mobile' => [
                'gateway_configuration' => [
                    'component' => 'cw_tpay_admin:visa_mobile:gateway_configuration',
                    'props' => [
                        'form' => '@=_context.form',
                        'paymentMethod' => '@=_context.resource',
                    ],
                ],
            ],
        ],
    ]);
};
