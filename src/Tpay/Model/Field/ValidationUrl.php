<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field;

use Tpay\OpenApi\Model\Fields\Field;

/**
 * @method string getValidationUrl()
 */
class ValidationUrl extends Field
{
    protected $name = 'validationUrl';
    protected $type = self::STRING;
}
