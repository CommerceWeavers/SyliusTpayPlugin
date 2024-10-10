<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\WebpackEncoreConfig;

return function(WebpackEncoreConfig $webpackEncore): void {
    $webpackEncore->builds('commerce_weavers_sylius_tpay_shop', '%kernel.project_dir%/public/build/commerce-weavers/tpay/shop');
};
