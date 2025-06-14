<?php

declare(strict_types=1);

namespace TestApp\Entity;

use CommerceWeavers\SyliusTpayPlugin\Model\ImageAwareTrait;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageAwareInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PaymentMethod as PaymentMethodBase;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method')]
class PaymentMethod extends PaymentMethodBase implements PaymentMethodImageAwareInterface
{
    use ImageAwareTrait;
}
