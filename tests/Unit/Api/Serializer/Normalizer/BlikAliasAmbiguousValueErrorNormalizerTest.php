<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Api\Serializer\Normalizer;

use ApiPlatform\Api\UrlGeneratorInterface;
use CommerceWeavers\SyliusTpayPlugin\Api\Exception\BlikAliasAmbiguousValueException;
use CommerceWeavers\SyliusTpayPlugin\Api\Serializer\Normalizer\BlikAliasAmbiguousValueErrorNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

final class BlikAliasAmbiguousValueErrorNormalizerTest extends TestCase
{
    use ProphecyTrait;

    private UrlGeneratorInterface|ObjectProphecy $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
    }

    /** @group requires-fixes */
    public function test_it_supports_jsonld_format_and_blik_alias_ambiguous_value_exception(): void
    {
        $result = $this->createTestSubject()->supportsNormalization(
            BlikAliasAmbiguousValueException::create([['applicationName' => 'testname', 'applicationCode' => 'testcode']]),
            'jsonld',
        );

        $this->assertTrue($result);
    }

    /** @group requires-fixes */
    public function test_it_supports_jsonld_format_and_flatten_exception_made_from_blik_alias_ambiguous_value_exception(): void
    {
        $result = $this->createTestSubject()->supportsNormalization(
            FlattenException::create(BlikAliasAmbiguousValueException::create([['applicationName' => 'testname', 'applicationCode' => 'testcode']])),
            'jsonld',
        );

        $this->assertTrue($result);
    }

    /** @group requires-fixes */
    public function test_it_does_not_support_format_other_than_jsonld(): void
    {
        $result = $this->createTestSubject()->supportsNormalization(
            BlikAliasAmbiguousValueException::create([['applicationName' => 'testname', 'applicationCode' => 'testcode']]),
            'html',
        );

        $this->assertFalse($result);
    }

    /** @group requires-fixes */
    public function test_it_does_not_support_all_exceptions(): void
    {
        $result = $this->createTestSubject()->supportsNormalization(
            new \Exception('Something went wrong!'),
            'jsonld',
        );

        $this->assertFalse($result);
    }

    /** @group requires-fixes */
    public function test_it_does_not_support_flatten_exception_made_from_any_exception(): void
    {
        $result = $this->createTestSubject()->supportsNormalization(
            FlattenException::create(new \Exception('Something went wrong!')),
            'jsonld',
        );

        $this->assertFalse($result);
    }

    /** @group requires-fixes */
    public function test_it_normalizes_blik_alias_ambiguous_value_exception(): void
    {
        $exception = BlikAliasAmbiguousValueException::create([['applicationName' => 'testname', 'applicationCode' => 'testcode']]);
        $flattenedException = FlattenException::create($exception);
        $this->urlGenerator->generate('api_jsonld_context', ['shortName' => 'Error'])->willReturn('/api/v2/context/Error');

        $result = $this->createTestSubject()->normalize($flattenedException, 'jsonld');

        $this->assertEquals([
            '@context' => '/api/v2/context/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Too many aliases found for a Blik alias. Specify one of the applications.',
            'applications' => [['applicationName' => 'testname', 'applicationCode' => 'testcode']],
        ], $result);
    }

    private function createTestSubject(): BlikAliasAmbiguousValueErrorNormalizer
    {
        return new BlikAliasAmbiguousValueErrorNormalizer($this->urlGenerator->reveal());
    }
}
