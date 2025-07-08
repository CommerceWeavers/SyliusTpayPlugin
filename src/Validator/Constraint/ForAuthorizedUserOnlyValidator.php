<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Validator\Constraint;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ForAuthorizedUserOnlyValidator extends ConstraintValidator
{
    public function __construct(private readonly Security $security)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ForAuthorizedUserOnly) {
            throw new UnexpectedTypeException($constraint, ForAuthorizedUserOnly::class);
        }

        if (null === $value || null !== $this->security->getUser()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->userNotAuthorizedErrorMessage)
            ->setCode($constraint::USER_NOT_AUTHORIZED_ERROR)
            ->addViolation()
        ;
    }
}
