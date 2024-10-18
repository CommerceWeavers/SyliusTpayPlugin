<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field;

use Tpay\OpenApi\Model\Fields\Field;

/**
 * @method string getDisplayName()
 */
class DisplayName extends Field
{
    protected $name = 'displayName';
    protected $type = self::STRING;
}
