<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Helper;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\SignatureVerifierInterface;

final class AlwaysValidSignatureVerifier implements SignatureVerifierInterface
{
    public function verify(string $jws, string $requestContent): bool
    {
        return true;
    }
}
