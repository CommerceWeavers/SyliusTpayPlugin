<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

final class VisaMobilePhoneDataTransformer implements DataTransformerInterface
{
    private const PREFIX = '48';

    public function transform($value): ?string
    {
        if ($value === null) {
            return '';
        }

        Assert::string($value);

        if (strlen($value) > 9 && str_starts_with($value, self::PREFIX)) {
            return substr($value, 2);
        }

        return $value;
    }

    public function reverseTransform($value): string
    {
        return self::PREFIX . $value;
    }
}
