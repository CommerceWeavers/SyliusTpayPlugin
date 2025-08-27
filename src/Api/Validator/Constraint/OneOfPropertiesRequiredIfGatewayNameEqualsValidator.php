<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class OneOfPropertiesRequiredIfGatewayNameEqualsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OneOfPropertiesRequiredIfGatewayNameEquals) {
            throw new UnexpectedTypeException($constraint, OneOfPropertiesRequiredIfGatewayNameEquals::class);
        }

        if (null === $constraint->gatewayName) {
            throw new MissingOptionsException(
                sprintf('Option "gatewayName" must be given for constraint "%s".', __CLASS__),
                ['gatewayName'],
            );
        }

        if (!$value instanceof Pay) {
            throw new UnexpectedTypeException($value, Pay::class);
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($value->orderToken);

        /** @var PaymentMethodInterface|null $lastPaymentMethod */
        $lastPaymentMethod = $order?->getLastPayment(PaymentInterface::STATE_NEW)?->getMethod();

        $gatewayName = $lastPaymentMethod?->getGatewayConfig()?->getFactoryName();

        if ($gatewayName !== $constraint->gatewayName) {
            return;
        }

        $propertyAccessor = new PropertyAccessor();

        foreach ($constraint->properties as $property) {
            $propertyValue = $propertyAccessor->getValue($value, $property);

            if (null !== $propertyValue && (!is_string($propertyValue) || '' !== trim($propertyValue))) {
                return;
            }
        }

        $this->context
            ->buildViolation($constraint->allFieldsAreBlankErrorMessage)
            ->setCode($constraint::ALL_FIELDS_ARE_BLANK_ERROR)
            ->addViolation()
        ;
    }
}
