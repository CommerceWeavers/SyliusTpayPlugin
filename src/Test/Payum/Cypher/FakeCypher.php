<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Test\Payum\Cypher;

use Payum\Core\Security\CypherInterface;

final class FakeCypher implements CypherInterface
{
    public function __construct(string $cypherKey)
    {
    }

    public function decrypt($value): string
    {
        return str_replace('encrypted_', '', $value);
    }

    public function encrypt($value): string
    {
        return sprintf('encrypted_%s', $value);
    }
}
