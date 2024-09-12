<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Contract\Tpay;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use PHPUnit\Framework\TestCase;
use Tpay\OpenApi\Api\TpayApi;

abstract class TpayTestCase extends TestCase
{
    use PHPMatcherAssertions;

    protected TpayApi $tpay;

    protected function setUp(): void
    {
        $this->tpay = new TpayApi(getenv('TPAY_CLIENT_ID'), getenv('TPAY_CLIENT_SECRET'));
    }
}
