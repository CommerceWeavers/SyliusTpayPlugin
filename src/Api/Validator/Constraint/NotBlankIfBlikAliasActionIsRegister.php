<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class NotBlankIfBlikAliasActionIsRegister extends Constraint
{
    public const FIELD_REQUIRED_ERROR = 'c084c60f-8c63-47fe-8a67-a8a961de7a8f';

    public string $fieldRequiredErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.field.not_blank';

    protected const ERROR_NAMES = [
        self::FIELD_REQUIRED_ERROR => 'FIELD_REQUIRED_ERROR',
    ];

    public function __construct(
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
        public ?string $blikAliasActionPropertyName = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
