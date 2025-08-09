<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tests\Unit\Customer\Resolver;

use CommerceWeavers\SyliusTpayPlugin\Customer\Resolver\UserAgentResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class UserAgentResolverTest extends TestCase
{

    public function test_it_resolves_user_agent_from_current_request(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', $userAgent);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new UserAgentResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertSame($userAgent, $result);
    }

    public function test_it_returns_null_when_no_current_request(): void
    {
        $requestStack = new RequestStack();
        $resolver = new UserAgentResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertNull($result);
    }

    public function test_it_returns_default_user_agent_when_no_user_agent_header(): void
    {
        $request = Request::create('/', 'GET');
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new UserAgentResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertSame('Symfony', $result);
    }

    public function test_it_handles_empty_user_agent_header(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', '');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $resolver = new UserAgentResolver($requestStack);

        $result = $resolver->resolve();

        $this->assertSame('', $result);
    }

    private function createTestSubject(): UserAgentResolver
    {
        return new UserAgentResolver(new RequestStack());
    }
}
