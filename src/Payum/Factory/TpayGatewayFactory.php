<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Factory;

use CommerceWeavers\SyliusTpayPlugin\CommerceWeaversSyliusTpayPlugin;
use CommerceWeavers\SyliusTpayPlugin\Tpay\TpayApi;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Tpay\OpenApi\Utilities\Cache;

class TpayGatewayFactory extends GatewayFactory
{
    public const NAME = 'tpay';

    public function __construct(
        private readonly Cache $cache,
        array $defaultConfig = [],
        ?GatewayFactoryInterface $coreGatewayFactory = null,
    ) {
        parent::__construct($defaultConfig, $coreGatewayFactory);
    }

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => $this->getName(),
            'payum.factory_title' => $this->getFactoryTitle(),
        ]);

        $config['payum.api'] = function (ArrayObject $config): TpayApi {
            /** @var array{client_id?: string, client_secret?: string, production_mode?: bool, notification_security_code?: string}|ArrayObject $config */
            $clientId = $config['client_id'] ?? '';
            $clientSecret = $config['client_secret'] ?? '';
            $productionMode = $config['production_mode'] ?? false;
            $notificationSecretCode = $config['notification_security_code'] ?? null;
            /** @var string $testApiUrl */
            $testApiUrl = getenv('TPAY_API_URL') !== false ? getenv('TPAY_API_URL') : null;

            return new TpayApi(
                $this->cache,
                $clientId,
                $clientSecret,
                $productionMode,
                apiUrlOverride: $testApiUrl,
                clientName: sprintf(
                    'sylius:%s|cw-tpay-sylius:%s|tpay-openapi-php:^1.8.0|PHP:%s',
                    SyliusCoreBundle::VERSION,
                    CommerceWeaversSyliusTpayPlugin::VERSION,
                    \PHP_VERSION,
                ),
                notificationSecretCode: $notificationSecretCode,
            );
        };
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getFactoryTitle(): string
    {
        return $this->getName();
    }
}
