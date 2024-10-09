<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiBankListProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class PayByLinkChannelIdAvailableValidator extends ConstraintValidator
{
    public const PAY_BY_LINK_CHANNEL_ID_FIELD_NAME = 'payByLinkChannelId';

    public function __construct(
        private readonly TpayApiBankListProviderInterface $apiBankListProvider
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, Pay::class);
        /** @var PayByLinkChannelIdAvailable $constraint */
        Assert::isInstanceOf($constraint, PayByLinkChannelIdAvailable::class);

        if (null === $value->payByLinkChannelId) {
            return;
        }

        $channelId = $value->payByLinkChannelId;

        $availableChannels = $this->apiBankListProvider->provide();

        foreach ($availableChannels as $availableChannel) {
            if ((int) $availableChannel['id'] === (int) $channelId) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->atPath(self::PAY_BY_LINK_CHANNEL_ID_FIELD_NAME)
            ->setCode($constraint::PAY_BY_LINK_CHANNEL_ID_AVAILABLE_ERROR)
            ->addViolation()
        ;
    }
}
