<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\PayByBlik;
use Symfony\Config\FrameworkConfig;

if (!defined('COMMERCE_WEAVERS_SYLIUS_TPAY_SYNC_TRANSPORT')) {
    define('COMMERCE_WEAVERS_SYLIUS_TPAY_SYNC_TRANSPORT', 'commerce_weavers_sylius_tpay_sync');
}

return function(FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport(COMMERCE_WEAVERS_SYLIUS_TPAY_SYNC_TRANSPORT)->dsn('sync://');

    $messenger->routing(PayByBlik::class)->senders([COMMERCE_WEAVERS_SYLIUS_TPAY_SYNC_TRANSPORT]);
};
