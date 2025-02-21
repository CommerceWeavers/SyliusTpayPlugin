<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Entity;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageInterface;
use Sylius\Component\Core\Model\Image;

class PaymentMethodImage extends Image implements PaymentMethodImageInterface
{
}
