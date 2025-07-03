<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\State\ApiResource\Error;
use ApiPlatform\Validator\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const FORMAT = 'jsonld';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly NormalizerInterface $decorated,
        private array $defaultContext = ['title' => 'An error occurred'],
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context = array_merge($this->defaultContext, $context);
        $normalized = $this->decorated->normalize($object, $format, $context);

        if (!is_array($normalized)) {
            return $normalized;
        }

        // Remove hydra-prefixed duplicate fields
        unset($normalized['hydra:title'], $normalized['hydra:description']);

        // Standardize error message field name to 'description' instead of 'detail'
        if (isset($normalized['detail'])) {
            $normalized['description'] = $normalized['detail'];
            unset($normalized['detail']);
        }

        return $normalized;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return
            self::FORMAT === $format &&
            $this->decorated->supportsNormalization($data, $format) &&
            (is_a($data, Error::class) || is_a($data, ValidationException::class));
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->decorated instanceof CacheableSupportsMethodInterface && $this->decorated->hasCacheableSupportsMethod();
    }
}
