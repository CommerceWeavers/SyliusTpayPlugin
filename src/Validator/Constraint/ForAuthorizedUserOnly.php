<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class ForAuthorizedUserOnly extends Constraint
{
    public const USER_NOT_AUTHORIZED_ERROR = 'c146928c-f22b-4802-ba90-5fb9952e7ee8';

    public string $userNotAuthorizedErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.field.user_not_authorized';

    protected const ERROR_NAMES = [
        self::USER_NOT_AUTHORIZED_ERROR => 'USER_NOT_AUTHORIZED_ERROR',
    ];

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
