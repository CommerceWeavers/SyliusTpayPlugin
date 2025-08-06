<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddSupportedRefundPaymentMethodPass implements CompilerPassInterface
{
    private const SUPPORTED_GATEWAYS_PARAM_NAME = 'sylius_refund.supported_gateways';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::SUPPORTED_GATEWAYS_PARAM_NAME)) {
            return;
        }

        /** @var array<string, mixed> $supportedGateways */
        $supportedGateways = $container->getParameter(self::SUPPORTED_GATEWAYS_PARAM_NAME);
        $supportedGateways[] = 'tpay_redirect';

        $container->setParameter(self::SUPPORTED_GATEWAYS_PARAM_NAME, $supportedGateways);
    }
}
