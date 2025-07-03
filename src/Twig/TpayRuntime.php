<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig;

use Payum\Core\Model\GatewayConfigInterface;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class TpayRuntime implements RuntimeExtensionInterface
{
    public function convertMinorToMajorCurrency(int $amount, int $currencyDecimals = 2): float
    {
        return $amount / 10 ** $currencyDecimals;
    }

    public function getConfigValue(GatewayConfigInterface $gatewayConfig, string $key): mixed
    {
        return $gatewayConfig->getConfig()[$key] ?? null;
    }
}
