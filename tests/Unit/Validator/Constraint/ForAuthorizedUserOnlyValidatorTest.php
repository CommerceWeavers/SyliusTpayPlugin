<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\Unit\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\ForAuthorizedUserOnly;
use CommerceWeavers\SyliusTpayPlugin\Validator\Constraint\ForAuthorizedUserOnlyValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class ForAuthorizedUserOnlyValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    private Security|ObjectProphecy $security;

    protected function setUp(): void
    {
        $this->security = $this->prophesize(Security::class);

        parent::setUp();
    }

    public function test_it_throws_an_exception_if_a_constraint_has_an_invalid_type(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(
            'hello',
            $this->prophesize(Constraint::class)->reveal(),
        );
    }

    public function test_it_does_not_build_violation_if_value_is_null(): void
    {
        $this->validator->validate(null, new ForAuthorizedUserOnly());

        $this->assertNoViolation();
    }

    public function test_it_does_not_build_violation_if_user_is_authorized(): void
    {
        $user = $this->prophesize(UserInterface::class);
        $this->security->getUser()->willReturn($user);

        $this->validator->validate('hello', new ForAuthorizedUserOnly());

        $this->assertNoViolation();
    }

    public function test_it_builds_violation_if_user_is_not_authorized(): void
    {
        $this->security->getUser()->willReturn(null);

        $this->validator->validate('hello', new ForAuthorizedUserOnly());

        $this
            ->buildViolation('commerce_weavers_sylius_tpay.shop.pay.field.user_not_authorized')
            ->setCode(ForAuthorizedUserOnly::USER_NOT_AUTHORIZED_ERROR)
            ->assertRaised()
        ;
    }

    protected function createValidator(): ForAuthorizedUserOnlyValidator
    {
        return new ForAuthorizedUserOnlyValidator($this->security->reveal());
    }
}
