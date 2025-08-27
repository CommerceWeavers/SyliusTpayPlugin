<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NotBlankIfGatewayNameEqualsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotBlankIfGatewayNameEquals) {
            throw new UnexpectedTypeException($constraint, NotBlankIfGatewayNameEquals::class);
        }

        if (null === $constraint->gatewayName) {
            throw new MissingOptionsException(
                sprintf('Option "gatewayName" must be given for constraint "%s".', __CLASS__),
                ['gatewayName'],
            );
        }

        $pay = $this->context->getObject();

        if (!$pay instanceof Pay) {
            throw new UnexpectedTypeException($pay, Pay::class);
        }

        if (null !== $value && (!is_string($value) || '' !== trim($value))) {
            return;
        }

        /** @var (OrderInterface&OrderLastNewPaymentAwareInterface)|null $order */
        $order = $this->orderRepository->findOneByTokenValue($pay->orderToken);

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $order?->getLastPayment(PaymentInterface::STATE_NEW)?->getMethod();
        $gatewayName = $paymentMethod?->getGatewayConfig()?->getFactoryName();

        if ($gatewayName !== $constraint->gatewayName) {
            return;
        }

        $this->context
            ->buildViolation($constraint->fieldRequiredErrorMessage)
            ->setCode($constraint::FIELD_REQUIRED_ERROR)
            ->addViolation()
        ;
    }
}
