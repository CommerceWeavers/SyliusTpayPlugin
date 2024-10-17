<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Entity;

use Sylius\Component\Core\Model\CustomerInterface;

class BlikAlias implements BlikAliasInterface
{
    private ?int $id = null;

    private ?string $value = null;

    private ?\DateTimeInterface $expirationDate = null;

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

    public function setExpirationDate(?\DateTimeInterface $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
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
