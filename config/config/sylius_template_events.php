<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sylius_ui', [
        'events' => [
            'cw.tpay.shop.select_payment.choice_item_form' => [
                'blocks' => [
                    'blik' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_blik.html.twig',
                        'priority' => 10,
                    ],
                    'card' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_card.html.twig',
                        'priority' => 10,
                    ],
                ],
            ],
            'sylius.shop.checkout.complete.summary' => [
                'blocks' => [
                    'blik' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_blik.html.twig',
                        'priority' => 5,
                    ],
                    'card' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_card.html.twig',
                        'priority' => 5,
                    ],
                    'pay-by-link' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_payByLink.html.twig',
                        'priority' => 5,
                    ],
                    'visa-mobile' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_visaMobile.html.twig',
                        'priority' => 5,
                    ]
                ],
            ],
            'sylius.shop.layout.javascripts' => [
                'blocks' => [
                    'commerce_weavers_sylius_tpay_scripts' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/scripts.html.twig',
                    ],
                ],
            ],
            'sylius.shop.layout.stylesheets' => [
                'blocks' => [
                    'commerce_weavers_sylius_tpay_styles' => [
                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/styles.html.twig',
                    ],
                ],
            ],
        ],
    ]);
};
