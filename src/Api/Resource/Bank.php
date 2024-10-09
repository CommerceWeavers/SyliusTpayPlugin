<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Resource;

class Bank
{
    public function __construct (
        private string $id,
        private string $name,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
