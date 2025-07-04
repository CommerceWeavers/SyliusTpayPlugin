<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.checkout.complete.content.form' => [
                'blik' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/blik.html.twig',
                    'priority' => 150,
                ],
                'pay_by_link' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/pay_by_link.html.twig',
                    'priority' => 150,
                ],
                'google_pay' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/google_pay.html.twig',
                    'priority' => 50,
                ],
                'apple_pay' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/apple_pay.html.twig',
                    'priority' => 50,
                ],
                'visa_mobile' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/visa_mobile.html.twig',
                    'priority' => 150,
                ],
            ],
        ],
    ]);
};
