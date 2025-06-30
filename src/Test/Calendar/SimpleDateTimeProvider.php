<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Test\Calendar;

use Sylius\Calendar\Provider\DateTimeProviderInterface;

final class SimpleDateTimeProvider implements DateTimeProviderInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}