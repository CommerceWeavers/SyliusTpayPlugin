<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Helper;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Verifier\ChecksumVerifierInterface;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;

final class AlwaysValidChecksumVerifier implements ChecksumVerifierInterface
{
    public function verify(BasicPayment $paymentData, string $merchantSecret): bool
    {
        return true;
    }
}
