<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class NotBlankIfGatewayNameEquals extends Constraint
{
    public const FIELD_REQUIRED_ERROR = '4102be3c-1c38-4cef-8265-714ad4968d9f';

    public string $fieldRequiredErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.field.not_blank';

    protected const ERROR_NAMES = [
        self::FIELD_REQUIRED_ERROR => 'FIELD_REQUIRED_ERROR',
    ];

    public function __construct(
        mixed $options = null,
        public ?string $gatewayName = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
