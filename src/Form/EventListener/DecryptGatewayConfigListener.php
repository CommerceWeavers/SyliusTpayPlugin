<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\EventListener;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Symfony\Component\Form\Event\PreSetDataEvent;

final readonly class DecryptGatewayConfigListener implements DecryptGatewayConfigListenerInterface
{
    public function __construct(
        private ?CypherInterface $cypher,
    ) {
    }

    public function __invoke(PreSetDataEvent $event): void
    {
        $gatewayConfig = $event->getData();

        if (!$gatewayConfig instanceof CryptedInterface || $this->cypher === null) {
            return;
        }

        $gatewayConfig->decrypt($this->cypher);

        $event->setData($gatewayConfig);
    }
}
