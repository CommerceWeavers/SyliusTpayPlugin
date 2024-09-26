<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Twig;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class TpayRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private CypherInterface $cypher,
        private TpayApiBankListProviderInterface $bankListProvider,
    ) {
    }

    public function getConfigValue(GatewayConfigInterface $gatewayConfig, string $key): mixed
    {
        if ($gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->decrypt($this->cypher);
        }

        return $gatewayConfig->getConfig()[$key] ?? null;
    }

    public function getAvailableBanks(): array
    {
        return $this->bankListProvider->provide();
    }
}
