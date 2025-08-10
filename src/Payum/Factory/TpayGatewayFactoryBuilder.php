<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Payum\Factory;

use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Payum\Core\GatewayFactoryInterface;
use Tpay\OpenApi\Utilities\Cache;

final class TpayGatewayFactoryBuilder extends GatewayFactoryBuilder
{
    public function __construct(
        private readonly Cache $cache,
        private readonly string $gatewayFactoryClass,
    ) {
        parent::__construct($this->gatewayFactoryClass);
    }

    public function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory): GatewayFactoryInterface
    {
        $gatewayFactoryClass = $this->gatewayFactoryClass;

        $gatewayFactory = new $gatewayFactoryClass($this->cache, $defaultConfig, $coreGatewayFactory);

        assert($gatewayFactory instanceof GatewayFactoryInterface);

        return $gatewayFactory;
    }
}
