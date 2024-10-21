<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Doctrine\QueryItemExtension\Provider;

interface AllowedOrderOperationsProviderInterface
{
    /**
     * @return array<string>
     */
    public function provide(): array;
}
