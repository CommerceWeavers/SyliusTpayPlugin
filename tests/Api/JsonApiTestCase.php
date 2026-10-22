<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Api;

use ApiTestCase\JsonApiTestCase as BaseJsonApiTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class JsonApiTestCase extends BaseJsonApiTestCase
{
    public const CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];

    public const PATCH_CONTENT_TYPE_HEADER = ['CONTENT_TYPE' => 'application/merge-patch+json', 'HTTP_ACCEPT' => 'application/json'];

    protected function generateBearerToken(UserInterface $user): string
    {
        /** @var JWTTokenManagerInterface $tokenManager */
        $tokenManager = $this->getContainer()->get(JWTTokenManagerInterface::class);

        return sprintf('Bearer %s', $tokenManager->create($user));
    }

    protected function generateAuthorizationHeader(UserInterface $user): array
    {
        return ['HTTP_Authorization' => $this->generateBearerToken($user)];
    }

    /**
     * @throws \Exception
     *
     * @param array<array-key, mixed> $expectedViolations
     */
    protected function assertResponseViolations(Response $response, array $expectedViolations): void
    {
        $this->assertResponseCode($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonHeader($response);
        $this->assertJsonResponseViolations($response, $expectedViolations);
    }

    /**
     * @throws \Exception
     *
     * @param array<array-key, mixed> $expectedViolations
     */
    protected function assertJsonResponseViolations(
        Response $response,
        array $expectedViolations,
        bool $assertViolationsCount = true,
    ): void {
        $responseContent = $response->getContent() ?: '';
        $this->assertNotEmpty($responseContent);
        $violations = json_decode($responseContent, true)['violations'] ?? [];

        if ($assertViolationsCount) {
            $this->assertCount(count($expectedViolations), $violations, $responseContent);
        }

        $violationMap = [];
        foreach ($violations as $violation) {
            $violationMap[$violation['propertyPath']][] = $violation['message'];
        }

        foreach ($expectedViolations as $expectedViolation) {
            $propertyPath = $expectedViolation['propertyPath'];
            $this->assertArrayHasKey($propertyPath, $violationMap, $responseContent);
            $this->assertContains($expectedViolation['message'], $violationMap[$propertyPath], $responseContent);
        }
    }
}
