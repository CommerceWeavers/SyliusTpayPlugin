<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

return function(RoutingConfigurator $routes): void {
    $routes->import(__DIR__.'/routes/routes_admin.php');
    $routes->import(__DIR__.'/routes/routes_shop.php');
    $routes->import(__DIR__.'/routes/routes_webhook.php');
};
