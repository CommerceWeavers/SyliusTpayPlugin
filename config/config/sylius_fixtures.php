<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\SyliusFixturesConfig;

return function(SyliusFixturesConfig $fixtures): void {
    $defaultSuite = $fixtures->suites('default');
    $defaultSuite->fixtures('shipping_method', [
        'options' => [
            'custom' => [
                'inpost_usa' => [
                    'code' => 'inpost_usa',
                    'name' => 'InPost',
                    'zone' => 'US',
                    'enabled' => true,
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'calculator' => [
                        'type' => 'flat_rate',
                        'configuration' => [
                            'FASHION_WEB' => [
                                'amount' => '0',
                            ],
                        ],
                    ],
                ],
                'inpost_world' => [
                    'code' => 'inpost_world',
                    'name' => 'InPost',
                    'zone' => 'WORLD',
                    'enabled' => true,
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'calculator' => [
                        'type' => 'flat_rate',
                        'configuration' => [
                            'FASHION_WEB' => [
                                'amount' => '0',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $defaultSuite->fixtures('payment_method', [
        'options' => [
            'custom' => [
                'tpay' => [
                    'code' => 'tpay',
                    'name' => 'Tpay',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'Tpay',
                    'gatewayConfig' => [
                        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
                        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
                        'type' => 'redirect',
                        'production_mode' => false,
                    ],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'card' => [
                    'code' => 'tpay_card',
                    'name' => 'Card (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'Tpay',
                    'gatewayConfig' => [
                        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
                        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
                        'cards_api' => '%env(string:TPAY_CARDS_API)%',
                        'type' => 'card',
                        'production_mode' => false,
                    ],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
            ],
        ],
    ]);
};