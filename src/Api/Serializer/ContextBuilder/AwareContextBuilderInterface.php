<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Serializer\ContextBuilder;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

interface AwareContextBuilderInterface extends SerializerContextBuilderInterface
{
    public function getAttributeKey(): string;

    public function getSupportedInterface(): string;

    public function getPropertyNameAccessorMethodName(): string;

    public function supports(Request $request, array $context, ?array $extractedAttributes): bool;
}
