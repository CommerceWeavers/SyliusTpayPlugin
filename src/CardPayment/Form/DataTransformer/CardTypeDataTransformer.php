<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

final class CardTypeDataTransformer implements DataTransformerInterface
{
    public function transform($value): ?array
    {
        return null;
    }

    /**
     * @param mixed|array{card: string} $value
     */
    public function reverseTransform(mixed $value): string
    {
        if (!is_array($value)) {
            return '';
        }

        return (string) ($value['card'] ?? '');
    }
}
