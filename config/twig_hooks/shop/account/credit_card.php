<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $configurator): void {
    $configurator->extension('sylius_twig_hooks', [
        'hooks' => [
            'cw.tpay.shop.account.credit_card.index.content' => [
                'breadcrumbs' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/account/credit_card/index/content/breadcrumbs.html.twig',
                    'priority' => 200,
                ],
            ],
            'cw.tpay.shop.account.credit_card.index.content.main' => [
                'title' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/account/credit_card/index/content/main/header/title.html.twig',
                    'priority' => 256,
                ],
                'subtitle' => [
                    'template' => '@CommerceWeaversSyliusTpayPlugin/shop/account/credit_card/index/content/main/header/subtitle.html.twig',
                    'priority' => 128,
                ],
                'grid' => [
                    'template' => '@SyliusShop/shared/grid/data_table.html.twig',
                ],
            ],
        ],
    ]);
};
