<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class NotBlankIfGatewayConfigTypeEquals extends Constraint
{
    public const FIELD_REQUIRED_ERROR = '275416a8-bd6f-4990-96ed-a2da514ce2f9';

    public string $fieldRequiredErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.field.not_blank';

    protected static $errorNames = [
        self::FIELD_REQUIRED_ERROR => 'FIELD_REQUIRED_ERROR',
    ];

    public function __construct(
        mixed $options = null,
        public ?string $paymentMethodType = null,
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
