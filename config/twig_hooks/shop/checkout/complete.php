<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.checkout.complete.content.form' => [
                'tpay_blik' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/blik.html.twig',
                    'priority' => 150,
                ],
                'tpay_pay_by_link' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/pay_by_link.html.twig',
                    'priority' => 150,
                ],
                'tpay_google_pay' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/google_pay.html.twig',
                    'priority' => 50,
                ],
                'tpay_apple_pay' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/apple_pay.html.twig',
                    'priority' => 50,
                ],
                'tpay_visa_mobile' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/visa_mobile.html.twig',
                    'priority' => 150,
                ],
                'tpay_card' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/card.html.twig',
                    'priority' => 150,
                ],
                'tpay_policies' => [
                    'component' => 'cw_tpay_shop:policies',
                    'props' => [
                        'paymentMethod' => '@=_context.resource.getLastCartPayment()?.getMethod()',
                    ],
                    'priority' => 75,
                ],
            ],
        ],
    ]);
};
