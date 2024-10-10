<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return function(FrameworkConfig $framework): void {
    $framework->assets()->package('commerce_weavers_sylius_tpay_shop', [
        'json_manifest_path' => '%kernel.project_dir%/public/build/commerce-weavers/tpay/shop/manifest.json',
    ]);
};
