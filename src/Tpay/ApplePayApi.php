<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Model\RequestBody\InitApplePay;
use Tpay\OpenApi\Api\ApiAction;

class ApplePayApi extends ApiAction
{
    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>|string
     */
    public function init(array $fields): array|string
    {
        return $this->run(static::POST, '/wallet/applepay/init', $fields, new InitApplePay());
    }
}
