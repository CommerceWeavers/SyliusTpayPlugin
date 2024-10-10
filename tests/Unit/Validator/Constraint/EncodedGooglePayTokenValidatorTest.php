<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\EncodedGooglePayToken;
use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\EncodedGooglePayTokenValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class EncodedGooglePayTokenValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            'eyJ3YXRjaE1lIjogImh0dHBzOi8vd3d3LnlvdXR1YmUuY29tL3dhdGNoP3Y9ZFF3NHc5V2dYY1EifQ==',
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_throws_an_exception_if_value_is_not_a_string(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(421, new EncodedGooglePayToken());
    }

    /** @dataProvider emptyDataProvider */
    public function test_it_does_not_build_violation_if_value_is_empty(mixed $value): void
    {
        $this->validator->validate($value, new EncodedGooglePayToken());

        $this->assertNoViolation();
    }

    public function test_it_does_not_build_violation_if_value_is_json_object_encoded_with_base64(): void
    {
        $this->validator->validate(
            'eyJ3YXRjaE1lIjogImh0dHBzOi8vd3d3LnlvdXR1YmUuY29tL3dhdGNoP3Y9ZFF3NHc5V2dYY1EifQ==',
            new EncodedGooglePayToken(),
        );

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_value_is_encoded_with_base64_but_it_is_not_json_object(): void
    {
        $this->validator->validate('YmxhaGJsYWhibGFo', $constraint = new EncodedGooglePayToken());

        $this
            ->buildViolation($constraint->notJsonEncodedErrorMessage)
            ->setCode($constraint::NOT_JSON_ENCODED_ERROR)
            ->assertRaised()
        ;
    }

    public function test_it_builds_violation_if_value_is_not_encoded_with_base64(): void
    {
        $this->validator->validate('weee!', $constraint = new EncodedGooglePayToken());

        $this
            ->buildViolation($constraint->notEncodedErrorMessage)
            ->setCode($constraint::NOT_ENCODED_ERROR)
            ->assertRaised()
        ;
    }

    protected function createValidator(): EncodedGooglePayTokenValidator
    {
        return new EncodedGooglePayTokenValidator();
    }

    private function emptyDataProvider(): array
    {
        return [
            [null],
            [''],
        ];
    }
}
