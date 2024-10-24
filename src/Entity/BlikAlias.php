<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Entity;

use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\Uid\Uuid;

class BlikAlias implements BlikAliasInterface
{
    private ?int $id = null;

    private ?string $value = null;

    private ?\DateTimeInterface $expirationDate = null;

    private bool $registered = false;

    private ?CustomerInterface $customer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }

    public function redefine(): void
    {
        $this->value = Uuid::v4()->toRfc4122();
        $this->registered = false;
        $this->expirationDate = null;
    }

    public function register(?\DateTimeInterface $expirationDate): void
    {
        $this->registered = true;
        $this->expirationDate = $expirationDate;
    }

    public function unregister(): void
    {
        $this->registered = false;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }
}
