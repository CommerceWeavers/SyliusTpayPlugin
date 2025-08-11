<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Customer\Resolver;

use Symfony\Component\HttpFoundation\RequestStack;

final class UserAgentResolver implements UserAgentResolverInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function resolve(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        return $request->headers->get('User-Agent');
    }
}
