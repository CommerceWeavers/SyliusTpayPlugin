<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\CardPayment\Form\DataTransformer;

use CommerceWeavers\SyliusTpayPlugin\CardPayment\Form\DataTransformer\CardTypeDataTransformer;
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
        $this->assertSame(null, $dataTransformer->transform([]));
    }

    public function test_it_returns_null_when_trying_to_reverse_transform_a_non_array(): void
    {
        $dataTransformer = $this->createTestSubject();

        self::assertNull($dataTransformer->reverseTransform(null));
        self::assertNull($dataTransformer->reverseTransform(true));
        self::assertNull($dataTransformer->reverseTransform('string'));
        self::assertNull($dataTransformer->reverseTransform(1));
        self::assertNull($dataTransformer->reverseTransform([]));
    }

    public function test_it_returns_null_when_trying_to_reverse_transform_an_array_with_a_card_value_null(): void
    {
        $dataTransformer = $this->createTestSubject();

        self::assertNull($dataTransformer->reverseTransform(['card' => null]));
    }

    public function test_it_returns_card_value_when_trying_to_reverse_transform_an_array_with_a_card_key(): void
    {
        $dataTransformer = $this->createTestSubject();

        $this->assertSame('wheee!', $dataTransformer->reverseTransform(['card' => 'wheee!']));
        $this->assertSame('123', $dataTransformer->reverseTransform(['card' => 123]));
    }

    private function createTestSubject(): DataTransformerInterface
    {
        return new CardTypeDataTransformer();
    }
}
