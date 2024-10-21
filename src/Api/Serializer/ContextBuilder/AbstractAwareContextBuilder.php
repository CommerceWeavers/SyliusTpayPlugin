<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

abstract class AbstractAwareContextBuilder implements AwareContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decoratedContextBuilder,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decoratedContextBuilder->createFromRequest($request, $normalization, $extractedAttributes);

        if (!$this->supports($request, $context, $extractedAttributes)) {
            return $context;
        }

        /** @var string $inputClass */
        $inputClass = $this->getInputClassFrom($context);
        $constructorArgumentName = $this->getConstructorArgumentName($inputClass);

        $context[AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS][$inputClass] = array_merge(
            $context[AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS][$inputClass] ?? [],
            [$constructorArgumentName => $request->attributes->get($this->getAttributeKey())],
        );

        return $context;
    }

    public function supports(Request $request, array $context, ?array $extractedAttributes): bool
    {
        $inputClass = $this->getInputClassFrom($context);

        if (null === $inputClass) {
            return false;
        }

        return is_a($inputClass, $this->getSupportedInterface(), true);
    }

    private function getInputClassFrom(array $context): ?string
    {
        return $context['input']['class'] ?? null;
    }

    private function getConstructorArgumentName(string $inputClass): string
    {
        if (!is_a($inputClass, $this->getSupportedInterface(), true)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" must implement "%s".', $inputClass, $this->getSupportedInterface()));
        }

        return [$inputClass, $this->getPropertyNameAccessorMethodName()]();
    }

    abstract public function getAttributeKey(): string;

    abstract public function getSupportedInterface(): string;

    abstract public function getPropertyNameAccessorMethodName(): string;
}
