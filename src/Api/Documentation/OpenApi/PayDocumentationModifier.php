<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Documentation\OpenApi;

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\OpenApi;
use CommerceWeavers\SyliusTpayPlugin\Api\Documentation\PayRequestBodyExampleFactory;
use Sylius\Bundle\ApiBundle\OpenApi\Documentation\DocumentationModifierInterface;

if (!interface_exists(DocumentationModifierInterface::class)) {
    return;
}

final class PayDocumentationModifier implements DocumentationModifierInterface
{
    public function __construct(private readonly string $apiShopRoutePrefix)
    {
    }

    public function modify(OpenApi $docs): OpenApi
    {
        $paths = $docs->getPaths();
        $path = sprintf('%s/orders/{tokenValue}/pay', $this->apiShopRoutePrefix);
        $pathItem = $paths->getPath($path);

        if (null === $pathItem) {
            return $docs;
        }

        $post = $pathItem->getPost();

        if (null === $post) {
            return $docs;
        }

        $requestBody = $post->getRequestBody();

        if (null === $requestBody) {
            return $docs;
        }

        $content = $requestBody->getContent();

        if (!isset($content['application/ld+json'])) {
            return $docs;
        }

        /** @var MediaType $mediaType */
        $mediaType = $content['application/ld+json'];

        $content['application/ld+json'] = $mediaType->withExamples(new \ArrayObject(PayRequestBodyExampleFactory::create()));

        $pathItem = $pathItem->withPost(
            $post->withRequestBody(
                $requestBody->withContent($content),
            ),
        );

        $paths->addPath($path, $pathItem);

        return $docs->withPaths($paths);
    }
}
