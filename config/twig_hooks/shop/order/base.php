<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.order#javascripts' => [
                'tpay_scripts' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/scripts.html.twig',
                ],
            ],
            'sylius_shop.order#stylesheets' => [
                'tpay_styles' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/styles.html.twig',
                ],
            ],
        ],
    ]);
};
