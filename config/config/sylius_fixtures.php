<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
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
                    'gatewayConfig' => $tpayConfig + ['cards_api' => '%env(string:TPAY_CARDS_API)%',],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'blik' => [
                    'code' => 'tpay_blik',
                    'name' => 'Blik (Tpay)',
                    'gatewayFactory' => 'tpay_blik',
                    'gatewayName' => 'tpay_blik',
                    'gatewayConfig' => $tpayConfig,
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'pbl' => [
                    'code' => 'tpay_pbl',
                    'name' => 'Pay by Link (Tpay)',
                    'gatewayFactory' => 'tpay_pbl',
                    'gatewayName' => 'tpay_pbl',
                    'gatewayConfig' => $tpayConfig,
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
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::APPLE_PAY],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
                'visa_mobile' => [
                    'code' => 'tpay_visa_mobile',
                    'name' => 'Visa mobile (Tpay)',
                    'gatewayFactory' => 'tpay',
                    'gatewayName' => 'tpay',
                    'gatewayConfig' => $tpayConfig + ['type' => PaymentType::VISA_MOBILE],
                    'channels' => [
                        'FASHION_WEB',
                    ],
                    'enabled' => true,
                ],
            ],
        ],
    ]);
};
