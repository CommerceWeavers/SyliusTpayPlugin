<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use Payum\Core\Security\CypherInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class BlikTokenRequiredValidator extends AbstractPayValidator
{
    public const BLIK_TOKEN_FIELD_NAME = 'blikToken';

    private const TYPE = 'type';

    private const BLIK = 'blik';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        CypherInterface $cypher,
    ) {
        parent::__construct($cypher);
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!is_object($value)) {
            throw new UnexpectedValueException($value, OrderInterface::class);
        }

        if (!is_a($value, Pay::class)) {
            throw new UnexpectedValueException($value, OrderInterface::class);
        }

        if (!is_a($constraint, BlikTokenRequired::class)) {
            throw new UnexpectedValueException($constraint, BlikTokenRequired::class);
        }

        if (null !== $value->blikToken) {
            return;
        }

        /** @var (OrderInterface&OrderLastNewPaymentAwareInterface)|null $order */
        $order = $this->orderRepository->findOneByTokenValue($value->orderToken);

        if (null === $order) {
            return;
        }

        /** @var array{type?: string} $config */
        $config = $this->getGatewayConfigFromOrder($order);

        if (!isset($config[self::TYPE]) || self::BLIK !== $config[self::TYPE]) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath(self::BLIK_TOKEN_FIELD_NAME)
            ->setCode($constraint::BLIK_TOKEN_REQUIRED_ERROR)
            ->addViolation()
        ;
    }
}
