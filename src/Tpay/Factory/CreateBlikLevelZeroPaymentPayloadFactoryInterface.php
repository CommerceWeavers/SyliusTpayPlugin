<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Factory;

use CommerceWeavers\SyliusTpayPlugin\Entity\BlikAliasInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface CreateBlikLevelZeroPaymentPayloadFactoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function createFrom(PaymentInterface $payment, ?BlikAliasInterface $blikAlias, string $notifyUrl, string $localeCode): array;
}
