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

    public function test_it_returns_an_empty_string_when_trying_to_reverse_transform_a_non_array(): void
    {
        $dataTransformer = $this->createTestSubject();

        $this->assertSame('', $dataTransformer->reverseTransform(null));
        $this->assertSame('', $dataTransformer->reverseTransform(true));
        $this->assertSame('', $dataTransformer->reverseTransform('string'));
        $this->assertSame('', $dataTransformer->reverseTransform(1));
        $this->assertSame('', $dataTransformer->reverseTransform([]));
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
