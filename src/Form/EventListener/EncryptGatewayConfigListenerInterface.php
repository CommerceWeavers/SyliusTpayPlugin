<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\Event\PostSubmitEvent;

interface EncryptGatewayConfigListenerInterface
{
    public function __invoke(PostSubmitEvent $event): void;
}
