<?php

declare(strict_types=1);

use App\Kernel;
use Tpay\OpenApi\Utilities\Logger;

$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'project_dir' => dirname(__DIR__),
];

require_once dirname(__DIR__, 3).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    // set up Tpay logger
    Logger::setLogPath(sprintf('%s/tpay_open_api_', $kernel->getLogDir()));

    return $kernel;
};
