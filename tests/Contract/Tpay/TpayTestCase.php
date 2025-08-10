<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Contract\Tpay;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tpay\OpenApi\Api\TpayApi;
use Tpay\OpenApi\Utilities\Cache;

abstract class TpayTestCase extends TestCase
{
    use PHPMatcherAssertions;

    protected TpayApi $tpay;

    protected function setUp(): void
    {
        $clientId = getenv('TPAY_CLIENT_ID') ?: throw new \RuntimeException('TPAY_CLIENT_ID environment variable is required to run Tpay contract test.');
        $clientSecret = getenv('TPAY_CLIENT_SECRET') ?: throw new \RuntimeException('TPAY_CLIENT_SECRET environment variable is required to run Tpay contract test.');

        $cache = new Cache(new ArrayAdapter());
        $this->tpay = new TpayApi($cache, $clientId, $clientSecret, productionMode: false);
    }
}
