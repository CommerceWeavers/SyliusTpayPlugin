<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tests\Unit\Customer\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\IpAddressResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class IpAddressResolverTest extends TestCase
{

    public function test_it_resolves_ip_address_from_current_request(): void
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1']);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new IpAddressResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertSame('192.168.1.1', $result);
    }

    public function test_it_returns_null_when_no_current_request(): void
    {
        $requestStack = new RequestStack();
        $resolver = new IpAddressResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertNull($result);
    }

    public function test_it_handles_x_forwarded_for_header(): void
    {
        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            ['HTTP_X_FORWARDED_FOR' => '203.0.113.1, 198.51.100.1', 'REMOTE_ADDR' => '192.168.1.1']
        );
        $request->setTrustedProxies(['192.168.1.1'], Request::HEADER_X_FORWARDED_FOR);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new IpAddressResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertSame('198.51.100.1', $result);
    }

    private function createTestSubject(): IpAddressResolver
    {
        return new IpAddressResolver(new RequestStack());
    }
}
