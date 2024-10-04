<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(__DIR__.'/Application/.env');

    foreach ($_ENV as $key => $value) {
        putenv(sprintf('%s=%s', $key, $value));
    }
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
