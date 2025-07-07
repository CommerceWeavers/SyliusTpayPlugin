<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class OneOfPropertiesRequiredIfGatewayConfigTypeEqualsValidator extends AbstractPayValidator
{
    private const TPAY_PAYMENT_METHOD_TYPE_NAME = 'type';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OneOfPropertiesRequiredIfGatewayConfigTypeEquals) {
            throw new UnexpectedTypeException($constraint, OneOfPropertiesRequiredIfGatewayConfigTypeEquals::class);
        }

        if (null === $constraint->paymentMethodType) {
            throw new MissingOptionsException(
                sprintf('Option "paymentMethodType" must be given for constraint "%s".', __CLASS__),
                ['paymentMethodType'],
            );
        }

        if (!$value instanceof Pay) {
            throw new UnexpectedTypeException($value, Pay::class);
        }

        /** @var (OrderInterface&OrderLastNewPaymentAwareInterface)|null $order */
        $order = $this->orderRepository->findOneByTokenValue($value->orderToken);

        if (null === $order) {
            return;
        }

        /** @var array{type?: string} $config */
        $config = $this->getGatewayConfigFromOrder($order);

        if (
            !isset($config[self::TPAY_PAYMENT_METHOD_TYPE_NAME]) ||
            $constraint->paymentMethodType !== $config[self::TPAY_PAYMENT_METHOD_TYPE_NAME]
        ) {
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
