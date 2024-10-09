<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Model\OrderLastNewPaymentAwareInterface;
use CommerceWeavers\SyliusTpayPlugin\Tpay\PaymentType;
use Payum\Core\Security\CypherInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Webmozart\Assert\Assert;

final class PayByLinkChannelIdRequiredValidator extends AbstractPayValidator
{
    public const PAY_BY_LINK_CHANNEL_ID_FIELD_NAME = 'payByLinkChannelId';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        CypherInterface $cypher,
    ) {
        parent::__construct($cypher);
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, Pay::class);
        /** @var PayByLinkChannelIdRequired $constraint */
        Assert::isInstanceOf($constraint, PayByLinkChannelIdRequired::class);

        if (null !== $value->payByLinkChannelId) {
            return;
        }

        /** @var (OrderInterface&OrderLastNewPaymentAwareInterface)|null $order */
        $order = $this->orderRepository->findOneByTokenValue($value->orderToken);

        if (null === $order) {
            return;
        }

        /** @var array{type?: string} $config */
        $config = $this->getGatewayConfigFromOrder($order);

        if (
            !isset($config[self::TYPE]) ||
            PaymentType::PAY_BY_LINK !== $config[self::TYPE]
        ) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath(self::PAY_BY_LINK_CHANNEL_ID_FIELD_NAME)
            ->setCode($constraint::PAY_BY_LINK_CHANNEL_ID_REQUIRED_ERROR)
            ->addViolation()
        ;
    }
}
