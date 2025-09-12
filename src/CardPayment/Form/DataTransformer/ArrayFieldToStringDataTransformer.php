<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

final class ArrayFieldToStringDataTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): null
    {
        return null;
    }

    public function reverseTransform(mixed $value): string
    {
        if (\is_array($value)) {
            return (string) ($value['card'] ?? '');
        }

        return '';
    }
}
