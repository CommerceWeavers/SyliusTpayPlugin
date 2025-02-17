<?php

declare(strict_types=1);

namespace App\Entity;

use CommerceWeavers\SyliusTpayPlugin\Model\ImageAwareTrait;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\PaymentMethod as PaymentMethodBase;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method')]
class PaymentMethod extends PaymentMethodBase implements ImageAwareInterface
{
    use ImageAwareTrait;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: PaymentMethodImage::class, cascade: ['persist'])]
    protected ?ImageInterface $image = null;
}
