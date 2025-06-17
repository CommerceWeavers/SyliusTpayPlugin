<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // TODO: Convert to Twig Hooks
//    $containerConfigurator->extension('sylius_ui', [
//        'events' => [
//            'cw.tpay.admin.payment_method.form' => [
//                'blocks' => [
//                    'test_connection' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/admin/payment_method/test_connection.html.twig',
//                        'priority' => 10,
//                    ],
//                ],
//            ],
//            'cw.tpay.shop.checkout.complete.navigation' => [
//                'blocks' => [
//                    'apple_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_apple_pay.html.twig',
//                        'priority' => 5,
//                    ],
//                    'google_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_google_pay.html.twig',
//                        'priority' => 5,
//                    ],
//                ],
//            ],
//            'cw.tpay.shop.select_payment.choice_item_form' => [
//                'blocks' => [
//                    'blik' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_blik.html.twig',
//                        'priority' => 10,
//                    ],
//                    'card' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_card.html.twig',
//                        'priority' => 10,
//                    ],
//                    'pay_by_link' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_payByLink.html.twig',
//                        'priority' => 10,
//                    ],
//                    'pay_by_link_channel' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_payByLinkChannel.html.twig',
//                        'priority' => 10,
//                    ],
//                    'google_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_google_pay_regulations.html.twig',
//                        'priority' => 5,
//                    ],
//                    'apple_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_apple_pay_regulations.html.twig',
//                        'priority' => 5,
//                    ],
//                    'visa_mobile' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/_visaMobile.html.twig',
//                        'priority' => 10,
//                    ]
//                ],
//            ],
//            'sylius.shop.checkout.complete.summary' => [
//                'blocks' => [
//                    'blik' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_blik.html.twig',
//                        'priority' => 5,
//                    ],
//                    'card' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_card.html.twig',
//                        'priority' => 5,
//                    ],
//                    'pay_by_link' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_payByLink.html.twig',
//                        'priority' => 5,
//                    ],
//                    'pay_by_link_channel' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_payByLinkChannel.html.twig',
//                        'priority' => 5,
//                    ],
//                    'visa_mobile' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_visaMobile.html.twig',
//                        'priority' => 5,
//                    ],
//                    'google_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_google_pay_regulations.html.twig',
//                        'priority' => 5,
//                    ],
//                    'apple_pay' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/_apple_pay_regulations.html.twig',
//                        'priority' => 5,
//                    ],
//                ],
//            ],
//            'cw.tpay.shop.account.credit_card.index.subcontent' => [
//                'blocks' => [
//                    'commerce_weavers_sylius_tpay_scripts' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/account/credit_card/index/_subcontent.html.twig',
//                    ],
//                ],
//            ],
//            'sylius.admin.layout.javascripts' => [
//                'blocks' => [
//                    'commerce_weavers_sylius_tpay_scripts' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/admin/scripts.html.twig',
//                    ],
//                ],
//            ],
//            'sylius.shop.layout.javascripts' => [
//                'blocks' => [
//                    'commerce_weavers_sylius_tpay_scripts' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/scripts.html.twig',
//                    ],
//                ],
//            ],
//            'sylius.shop.layout.stylesheets' => [
//                'blocks' => [
//                    'commerce_weavers_sylius_tpay_styles' => [
//                        'template' => '@CommerceWeaversSyliusTpayPlugin/shop/styles.html.twig',
//                    ],
//                ],
//            ],
//        ],
//    ]);
};
