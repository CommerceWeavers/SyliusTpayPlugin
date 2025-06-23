<?php

declare(strict_types=1);

namespace TestApp\Repository;

use CommerceWeavers\SyliusTpayPlugin\Repository\PaymentMethodRepositoryInterface;
use CommerceWeavers\SyliusTpayPlugin\Repository\PaymentMethodTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository as BasePaymentMethodRepository;

final class PaymentMethodRepository extends BasePaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    use PaymentMethodTrait;
}
