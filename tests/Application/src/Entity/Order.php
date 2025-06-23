<?php

declare(strict_types=1);

namespace App\Entity;

use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderLastNewPaymentAwareInterface
{
    use OrderLastNewPaymentAwareTrait;
}
