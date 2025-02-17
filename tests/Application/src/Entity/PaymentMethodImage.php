<?php

declare(strict_types=1);

namespace App\Entity;

use Sylius\Component\Core\Model\Image;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Resource\Metadata\AsResource;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method_image')]
class PaymentMethodImage extends Image
{
    #[ORM\OneToOne(inversedBy: 'image', targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $owner;
}
