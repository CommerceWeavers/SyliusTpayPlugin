<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ImageInterface;

trait ImageAwareTrait
{
    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: PaymentMethodImageInterface::class, cascade: ['persist'])]
    protected ?PaymentMethodImageInterface $image = null;

    #[ORM\Column(name: 'default_image_url', nullable: true)]
    protected ?string $defaultImageUrl = null;

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): void
    {
        $image?->setOwner($this);

        $this->image = $image;
    }

    public function getDefaultImageUrl(): ?string
    {
        return $this->defaultImageUrl;
    }

    public function setDefaultImageUrl(?string $defaultImageUrl): void
    {
        $this->defaultImageUrl = $defaultImageUrl;
    }
}
