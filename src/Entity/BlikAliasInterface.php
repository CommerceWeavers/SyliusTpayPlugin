<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Entity;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Model\ResourceInterface;

interface BlikAliasInterface extends ResourceInterface
{
    public function getValue(): ?string;

    public function setValue(?string $value): void;

    public function getExpirationDate(): ?\DateTimeInterface;

    public function isRegistered(): bool;

    public function redefine(): void;

    public function register(?\DateTimeInterface $expirationDate): void;

    public function unregister(): void;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): void;
}
