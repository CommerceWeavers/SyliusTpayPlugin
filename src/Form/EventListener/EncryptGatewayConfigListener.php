<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\Event\PostSubmitEvent;

final readonly class EncryptGatewayConfigListener implements EncryptGatewayConfigListenerInterface
{
    public function __invoke(PostSubmitEvent $event): void
    {
        // No encryption needed - pass through as-is
    }
}
