<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class OneOfPropertiesRequiredIfGatewayConfigTypeEquals extends Constraint
{
    public const ALL_FIELDS_ARE_BLANK_ERROR = '36adfa66-af1b-4f93-8b0b-9b92a7e1c1a1';

    public string $allFieldsAreBlankErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.fields_required';

    protected const ERROR_NAMES = [
        self::ALL_FIELDS_ARE_BLANK_ERROR => 'ALL_FIELDS_ARE_BLANK_ERROR',
    ];

    public function __construct(
        mixed $options = null,
        public ?string $paymentMethodType = null,
        public array $properties = [],
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
