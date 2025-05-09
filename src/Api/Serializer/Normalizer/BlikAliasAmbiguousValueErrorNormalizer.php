<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Problem\Serializer\ErrorNormalizerTrait;
use CommerceWeavers\SyliusTpayPlugin\Api\Exception\BlikAliasAmbiguousValueException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class BlikAliasAmbiguousValueErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    use ErrorNormalizerTrait;

    public const FORMAT = 'jsonld';

    public const TITLE = 'title';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private array $defaultContext = [self::TITLE => 'An error occurred'],
    ) {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * @param mixed|BlikAliasAmbiguousValueException|FlattenException $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        Assert::isInstanceOfAny($object, [\Throwable::class, FlattenException::class]);
        $exceptionMessage = (array) json_decode($object->getMessage(), true);
        $description = $exceptionMessage['description'] ?? null;
        $applications = $exceptionMessage['applications'] ?? [];

        return [
            '@context' => $this->urlGenerator->generate('api_jsonld_context', ['shortName' => 'Error']),
            '@type' => 'hydra:Error',
            'hydra:title' => $context[self::TITLE] ?? $this->defaultContext[self::TITLE],
            'hydra:description' => $description,
            'applications' => $applications,
        ];
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return
            self::FORMAT === $format &&
            (
                $data instanceof BlikAliasAmbiguousValueException ||
                (
                    $data instanceof FlattenException &&
                    $data->getClass() === BlikAliasAmbiguousValueException::class
                )
            )
        ;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
