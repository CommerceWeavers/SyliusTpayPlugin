<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkPayment\Form\Type;

use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\DecryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\EventListener\EncryptGatewayConfigListenerInterface;
use CommerceWeavers\SyliusTpayPlugin\Form\Type\AbstractTpayGatewayConfigurationType;

final class GatewayConfigurationType extends AbstractTpayGatewayConfigurationType
{
    public function __construct(
        DecryptGatewayConfigListenerInterface $decryptGatewayConfigListener,
        EncryptGatewayConfigListenerInterface $encryptGatewayConfigListener,
    ) {
        parent::__construct($decryptGatewayConfigListener, $encryptGatewayConfigListener);
    }
}
