<?php

declare(strict_types=1);

namespace App\Entity;

use CommerceWeavers\SyliusTpayPlugin\Model\ImageAwareTrait;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageAwareInterface;
use Sylius\Component\Core\Model\PaymentMethod as PaymentMethodBase;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method')]
class PaymentMethod extends PaymentMethodBase implements PaymentMethodImageAwareInterface
{
    use ImageAwareTrait;
}
