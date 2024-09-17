<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Factory;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Tpay\OpenApi\Api\TpayApi;

class TpayGatewayFactory extends GatewayFactory
{
    public const NAME = 'tpay';

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::NAME,
            'payum.factory_title' => self::NAME,
        ]);

        $config['payum.api'] = function (ArrayObject $config): TpayApi {
            /** @var array{client_id?: string, client_secret?: string, production_mode?: bool} $config */
            $clientId = $config['client_id'] ?? null;
            $clientSecret = $config['client_secret'] ?? null;
            $productionMode = $config['production_mode'] ?? false;

            if (null === $clientId || null === $clientSecret) {
                throw new \InvalidArgumentException('Tpay ClientId and ClientSecret are required.');
            }

            return new TpayApi($clientId, $clientSecret, $productionMode);
        };
    }
}
