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
            ],
            'sylius_shop.shared.form.select_payment.payment.choice.details' => [
                'payment_image' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/checkout/select_payment/payment/choice/details/image.html.twig',
                ],
            ],
        ],
    ]);
};
