<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\Event\PreSetDataEvent;

final readonly class DecryptGatewayConfigListener implements DecryptGatewayConfigListenerInterface
{
    public function __invoke(PreSetDataEvent $event): void
    {
        // No decryption needed - pass through as-is
    }
}
