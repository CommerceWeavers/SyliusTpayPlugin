<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Payum\Core\Security\CypherInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NotBlankIfGatewayConfigTypeEqualsValidator extends AbstractPayValidator
{
    private const TPAY_PAYMENT_METHOD_TYPE_NAME = 'type';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        CypherInterface $cypher,
    ) {
        parent::__construct($cypher);
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotBlankIfGatewayConfigTypeEquals) {
            throw new UnexpectedTypeException($constraint, NotBlankIfGatewayConfigTypeEquals::class);
        }

        if (null === $constraint->paymentMethodType) {
            throw new MissingOptionsException(
                sprintf('Option "paymentMethodType"must be given for constraint "%s".', __CLASS__),
                ['paymentMethodType'],
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

        $this->context
            ->buildViolation($constraint->fieldRequiredErrorMessage)
            ->setCode($constraint::FIELD_REQUIRED_ERROR)
            ->addViolation()
        ;
    }
}
