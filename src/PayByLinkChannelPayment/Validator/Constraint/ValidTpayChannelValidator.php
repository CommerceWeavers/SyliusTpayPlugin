<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\PayByLinkChannelPayment\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\ValidTpayChannelListProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class ValidTpayChannelValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ValidTpayChannelListProviderInterface $validatedTpayApiBankListProvider,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        Assert::string($value);

        Assert::isInstanceOf($constraint, ValidTpayChannel::class);

        $validChannels = $this->validatedTpayApiBankListProvider->provide();

        if (array_key_exists($value, $validChannels)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setCode($constraint::NOT_VALID_CHANNEL_ERROR)
            ->addViolation()
        ;
    }
}
