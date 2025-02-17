<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Model;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ImageInterface;

trait ImageAwareTrait
{
    protected ?ImageInterface $image = null;

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): void
    {
        $image?->setOwner($this);

        $this->image = $image;
    }
}
