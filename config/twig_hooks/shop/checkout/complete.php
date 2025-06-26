<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.checkout.complete.content.form' => [
                'pay_by_link' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/cart/complete/pay_by_link.html.twig',
                ],
                'channels_picker' => [
                    'component' => 'cw_tpay_shop:pay_by_link:channel_picker',
                    'props' => [
                        'order' => '@=_context.resource',
                    ],
                    'priority' => 150,
                ],
            ],
        ],
    ]);
};
