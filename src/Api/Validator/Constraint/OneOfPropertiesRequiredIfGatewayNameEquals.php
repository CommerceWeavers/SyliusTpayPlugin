<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class OneOfPropertiesRequiredIfGatewayNameEquals extends Constraint
{
    public const ALL_FIELDS_ARE_BLANK_ERROR = 'c64630dd-3766-4a69-9d83-66aabf8f68fe';

    public string $allFieldsAreBlankErrorMessage = 'commerce_weavers_sylius_tpay.shop.pay.fields_required';

    protected const ERROR_NAMES = [
        self::ALL_FIELDS_ARE_BLANK_ERROR => 'ALL_FIELDS_ARE_BLANK_ERROR',
    ];

    public function __construct(
        mixed $options = null,
        public ?string $gatewayName = null,
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
