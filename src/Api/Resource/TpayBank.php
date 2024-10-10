<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Resource;

class TpayBank
{
    public function __construct(
        private ?string $id,
        private ?string $name,
        private ?string $fullName,
        private ?array $image,
        private ?string $available,
        private ?bool $onlinePayment,
        private ?bool $instantRedirection,
        private ?array $groups,
        private ?array $constraints,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getImage(): ?array
    {
        return $this->image;
    }

    public function setImage(?array $image): void
    {
        $this->image = $image;
    }

    public function getAvailable(): ?string
    {
        return $this->available;
    }

    public function setAvailable(?string $available): void
    {
        $this->available = $available;
    }

    public function getOnlinePayment(): ?bool
    {
        return $this->onlinePayment;
    }

    public function setOnlinePayment(?bool $onlinePayment): void
    {
        $this->onlinePayment = $onlinePayment;
    }

    public function getInstantRedirection(): ?bool
    {
        return $this->instantRedirection;
    }

    public function setInstantRedirection(?bool $instantRedirection): void
    {
        $this->instantRedirection = $instantRedirection;
    }

    public function getGroups(): ?array
    {
        return $this->groups;
    }

    public function setGroups(?array $groups): void
    {
        $this->groups = $groups;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function setConstraints(?array $constraints): void
    {
        $this->constraints = $constraints;
    }

    public static function FromArray(mixed $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? null,
            $data['fullName'] ?? null,
            $data['image'] ?? null,
            $data['available'] ?? null,
            $data['onlinePayment'] ?? null,
            $data['instantRedirection'] ?? null,
            $data['groups'] ?? null,
            $data['constraints'] ?? null,
        );

    }
}
