<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Symfony\Component\Form\Event\PreSetDataEvent;

interface DecryptGatewayConfigListenerInterface
{
    public function __invoke(PreSetDataEvent $event): void;
}
