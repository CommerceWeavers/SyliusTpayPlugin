<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
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

    $tpayConfig = [
        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
        'cards_api' => '%env(string:TPAY_CARDS_API)%',
        'notification_security_code' => '%env(string:TPAY_NOTIFICATION_SECURITY_CODE)%',
        'google_merchant_id' => '%env(string:TPAY_GOOGLE_MERCHANT_ID)%',
        'merchant_id' => '%env(string:TPAY_MERCHANT_ID)%',
        'production_mode' => false,
    ];

    $defaultSuite->fixtures('payment_method', [
        'options' => [
            'custom' => [
                'tpay' => [
                    'code' => 'tpay',
                    'name' => 'Tpay',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::REDIRECT],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'card' => [
                    'code' => 'tpay_card',
                    'name' => 'Card (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::CARD],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'blik' => [
                    'code' => 'tpay_blik',
                    'name' => 'Blik (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::BLIK],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'pbl' => [
                    'code' => 'tpay_pbl',
                    'name' => 'Pay by Link (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::PAY_BY_LINK],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'google_pay' => [
                    'code' => 'tpay_google_pay',
                    'name' => 'Google Pay (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::GOOGLE_PAY],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'apple_pay' => [
                    'code' => 'tpay_apple_pay',
                    'name' => 'Apple Pay (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => [
                        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
                        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
                        'notification_security_code' => '%env(string:TPAY_NOTIFICATION_SECURITY_CODE)%',
                        'merchant_id' => '%env(string:TPAY_MERCHANT_ID)%',
                        'type' => PaymentType::APPLE_PAY,
                        'production_mode' => false,
                    ],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'visa_mobile' => [
                    'code' => 'visa_mobile',
                    'name' => 'Visa mobile (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => [
                        'client_id' => '%env(string:TPAY_CLIENT_ID)%',
                        'client_secret' => '%env(string:TPAY_CLIENT_SECRET)%',
                        'type' => 'visa-mobile',
                        'notification_security_code' => '%env(string:TPAY_NOTIFICATION_SECURITY_CODE)%',
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
