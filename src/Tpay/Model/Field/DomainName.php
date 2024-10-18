<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field;

use Tpay\OpenApi\Model\Fields\Field;

/**
 * @method string getDomainName()
 */
class DomainName extends Field
{
    protected $name = 'domainName';

    protected $type = self::STRING;
}
