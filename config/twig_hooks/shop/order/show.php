<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.order.show.content.form.select_payment.payment.choice.tpay_blik.details' => [
                'blik' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/blik.html.twig',
                    'priority' => -300,
                ],
            ],
            'sylius_shop.order.show.content.form.select_payment.payment.choice.tpay_pbl.details' => [
                'pay_by_link' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/pay_by_link.html.twig',
                    'priority' => -300,
                ],
            ],
            'sylius_shop.order.show.content.form.select_payment.payment.choice.tpay_visa_mobile.details' => [
                'visa_mobile' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/visa_mobile.html.twig',
                    'priority' => -300,
                ],
            ],
            'sylius_shop.order.show.content.form.select_payment.payment.choice.tpay_card.details' => [
                'card' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/order/pay/card.html.twig',
                    'priority' => -300,
                ],
            ],
            'sylius_shop.order.show.content.form.select_payment.payment.choice.details' => [
                'tpay_policies' => [
                    'component' => 'cw_tpay_shop:policies',
                    'props' => [
                        'paymentMethod' => '@=_context.method'
                    ],
                    'priority' => -400,
                ],
            ],
        ],
    ]);
};
