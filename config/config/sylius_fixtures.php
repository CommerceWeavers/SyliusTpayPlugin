<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\SyliusFixturesConfig;

return static function(SyliusFixturesConfig $fixtures): void {
    $defaultSuite = $fixtures->suites('default');
    $defaultSuite->fixtures('channel', [
        'options' => [
            'custom' => [
                'fashion_web_store' => [
                    'base_currency' => 'PLN',
                    'currencies' => ['PLN'],
                    'locales' => ['pl_PL'],
                    'default_locale' => 'pl_PL',
                ],
            ],
        ],
    ]);
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

    $tpayConfig = [
        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
        'notification_security_code' => '%env(string:TPAY_NOTIFICATION_SECURITY_CODE)%',
        'google_merchant_id' => '%env(string:TPAY_GOOGLE_MERCHANT_ID)%',
        'merchant_id' => '%env(string:TPAY_MERCHANT_ID)%',
        'production_mode' => false,
    ];

    $defaultSuite->fixtures('payment_method', [
        'options' => [
            'custom' => [
                'tpay' => [
                    'code' => 'tpay_redirect',
                    'name' => 'Tpay (Redirect)',
                    'gatewayFactory' => 'tpay_redirect',
                    'gatewayName' => 'tpay_redirect',
                    'gatewayConfig' => $tpayConfig,
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'card' => [
                    'code' => 'tpay_card',
                    'name' => 'Card (Tpay)',
                    'gatewayFactory' => 'tpay_card',
                    'gatewayName' => 'tpay_card',
                    'gatewayConfig' => $tpayConfig + ['cards_api' => '%env(string:TPAY_CARDS_API)%', 'tpay_channel_id' => '53'],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                    'defaultImageUrl' => 'https://secure.sandbox.tpay.com/tpay/web/channels/53/normal-white-bg.png',
                ],
                'blik' => [
                    'code' => 'tpay_blik',
                    'name' => 'Blik (Tpay)',
                    'gatewayFactory' => 'tpay_blik',
                    'gatewayName' => 'tpay_blik',
                    'gatewayConfig' => $tpayConfig + ['tpay_channel_id' => '64'],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                    'defaultImageUrl' => 'https://secure.sandbox.tpay.com/tpay/web/channels/64/normal-white-bg.png',
                ],
                'pbl' => [
                    'code' => 'tpay_pbl',
                    'name' => 'Pay-by-link (Tpay)',
                    'gatewayFactory' => 'tpay_pbl',
                    'gatewayName' => 'tpay_pbl',
                    'gatewayConfig' => $tpayConfig,
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'pbl_channel' => [
                    'code' => 'tpay_pbl_channel',
                    'name' => 'Pay-by-link single channel (Tpay)',
                    'gatewayFactory' => 'tpay_pbl_channel',
                    'gatewayName' => 'tpay_pbl_channel',
                    'gatewayConfig' => $tpayConfig + [
                        'tpay_channel_id' => '%env(string:TPAY_PBL_CHANNEL_ID)%',
                    ],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'google_pay' => [
                    'code' => 'tpay_google_pay',
                    'name' => 'Google Pay (Tpay)',
                    'gatewayFactory' => 'tpay_google_pay',
                    'gatewayName' => 'tpay_google_pay',
                    'gatewayConfig' => $tpayConfig + ['tpay_channel_id' => '68'],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                    'defaultImageUrl' => 'https://secure.sandbox.tpay.com/tpay/web/channels/68/normal-white-bg.png',
                ],
                'apple_pay' => [
                    'code' => 'tpay_apple_pay',
                    'name' => 'Apple Pay (Tpay)',
                    'gatewayFactory' => 'tpay_apple_pay',
                    'gatewayName' => 'tpay_apple_pay',
                    'gatewayConfig' => $tpayConfig + ['tpay_channel_id' => '75'],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                    'defaultImageUrl' => 'https://secure.sandbox.tpay.com/tpay/web/channels/75/normal-white-bg.png',
                ],
                'visa_mobile' => [
                    'code' => 'tpay_visa_mobile',
                    'name' => 'Visa mobile (Tpay)',
                    'gatewayFactory' => 'tpay_visa_mobile',
                    'gatewayName' => 'tpay_visa_mobile',
                    'gatewayConfig' => $tpayConfig + ['tpay_channel_id' => '79'],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                    'defaultImageUrl' => 'https://secure.sandbox.tpay.com/tpay/web/channels/79/normal-white-bg.png',
                ],
            ],
        ],
    ]);
};
