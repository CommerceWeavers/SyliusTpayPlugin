<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Form\DataTransformer;

use CommerceWeavers\SyliusTpayPlugin\Form\DataTransformer\CardTypeDataTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataTransformerInterface;

final class CardTypeDataTransformerTest extends TestCase
{
    public function test_it_returns_null_on_a_transform(): void
    {
        $dataTransformer = $this->createTestSubject();

        $this->assertSame(null, $dataTransformer->transform(null));
        $this->assertSame(null, $dataTransformer->transform(true));
        $this->assertSame(null, $dataTransformer->transform('string'));
        $this->assertSame(null, $dataTransformer->transform(1));
    }

    public function test_it_returns_an_empty_string_when_trying_to_reverse_transform_a_non_array(): void
    {
        $dataTransformer = $this->createTestSubject();

        $this->assertSame('', $dataTransformer->reverseTransform(null));
        $this->assertSame('', $dataTransformer->reverseTransform(true));
        $this->assertSame('', $dataTransformer->reverseTransform('string'));
        $this->assertSame('', $dataTransformer->reverseTransform(1));
    }

    private function createTestSubject(): DataTransformerInterface
    {
        return new CardTypeDataTransformer();
    }
}
