<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.checkout#stylesheets' => [
                'tpay_complete_checkout' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/styles.html.twig',
                ],
            ],
        ],
    ]);
};
