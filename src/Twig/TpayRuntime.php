<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class TpayRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ?CypherInterface $cypher,
    ) {
    }

    public function convertMinorToMajorCurrency(int $amount, int $currencyDecimals = 2): float
    {
        return $amount / 10 ** $currencyDecimals;
    }

    public function getConfigValue(GatewayConfigInterface $gatewayConfig, string $key): mixed
    {
        if ($this->cypher !== null && $gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->decrypt($this->cypher);
        }

        return $gatewayConfig->getConfig()[$key] ?? null;
    }
}
