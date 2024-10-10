<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class EncodedGooglePayTokenValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EncodedGooglePayToken) {
            throw new UnexpectedTypeException($constraint, EncodedGooglePayToken::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $decodedValue = base64_decode($value);

        if (base64_encode($decodedValue) !== $value) {
            $this->context
                ->buildViolation($constraint->notEncodedErrorMessage)
                ->setCode($constraint::NOT_ENCODED_ERROR)
                ->addViolation()
            ;

            return;
        }

        if ($this->isJsonValid($decodedValue)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->notJsonEncodedErrorMessage)
            ->setCode($constraint::NOT_JSON_ENCODED_ERROR)
            ->addViolation()
        ;
    }

    private function isJsonValid(string $json): bool
    {
        try {
            json_decode($json, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        return true;
    }
}
