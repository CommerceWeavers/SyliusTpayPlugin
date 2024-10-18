<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Model\RequestBody;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field\DisplayName;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field\DomainName;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Model\Field\ValidationUrl;
use Tpay\OpenApi\Model\Objects\Objects;

class InitApplePay extends Objects
{
    const OBJECT_FIELDS = [
        'domainName' => DomainName::class,
        'displayName' => DisplayName::class,
        'validationUrl' => ValidationUrl::class,
    ];

    /** @var DomainName */
    public $domainName;

    /** @var DisplayName */
    public $displayName;

    /** @var ValidationUrl */
    public $validationUrl;
}
