<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasExpiredException;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\PreconditionGuard\Exception\BlikAliasNotRegisteredException;
use Symfony\Component\Clock\ClockInterface;

final class ActiveBlikAliasPreconditionGuard implements ActiveBlikAliasPreconditionGuardInterface
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function denyIfNotActive(BlikAliasInterface $blikAlias): void
    {
        if (!$blikAlias->isRegistered()) {
            throw new BlikAliasNotRegisteredException();
        }

        if ($blikAlias->getExpirationDate() < $this->clock->now()) {
            throw new BlikAliasExpiredException();
        }
    }
}
