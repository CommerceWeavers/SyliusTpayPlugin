<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Tpay\Security\Notification\Resolver;

final class CertificateResolver implements CertificateResolverInterface
{
    public function resolve(string $x5u): string
    {
        $ch = curl_init();

        if (false === $ch) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        if ('' === $x5u) {
            throw new \RuntimeException('URL cannot be empty');
        }

        curl_setopt($ch, \CURLOPT_URL, $x5u);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!is_string($result)) {
            throw new \RuntimeException('Failed to fetch certificate from URL: ' . $x5u);
        }

        return $result;
    }
}
