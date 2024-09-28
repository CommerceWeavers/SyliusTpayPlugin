<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class TpayRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private CypherInterface $cypher,
    ) {
    }

    public function getConfigValue(GatewayConfigInterface $gatewayConfig, string $key): mixed
    {
        if ($gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->decrypt($this->cypher);
        }

        return $gatewayConfig->getConfig()[$key] ?? null;
    }
}
