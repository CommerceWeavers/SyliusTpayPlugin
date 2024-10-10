<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Api\Validator\Constraint;

use CommerceWeavers\SyliusTpayPlugin\Api\Command\Pay;
use CommerceWeavers\SyliusTpayPlugin\Api\Resource\TpayChannel;
use CommerceWeavers\SyliusTpayPlugin\Tpay\Provider\TpayApiChannelListProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class TpayChannelIdEligibilityValidator extends ConstraintValidator
{
    public const PAY_BY_LINK_CHANNEL_ID_FIELD_NAME = 'tpayChannelId';

    public function __construct(
        private readonly TpayApiChannelListProviderInterface $tpayApiChannelListProvider,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, Pay::class);
        /** @var TpayChannelIdEligibility $constraint */
        Assert::isInstanceOf($constraint, TpayChannelIdEligibility::class);

        if (null === $value->tpayChannelId) {
            return;
        }

        $channelId = $value->tpayChannelId;

        $apiChannels = $this->tpayApiChannelListProvider->provide();

        $channel = null;

        foreach ($apiChannels as $apiChannel) {
            if ((int) $apiChannel['id'] === (int) $channelId) {
                $channel = TpayChannel::FromArray($apiChannel);

                break;
            }
        }

        if (null === $channel) {
            $this->context->buildViolation($constraint->doesNotExistMessage)
                ->atPath(self::PAY_BY_LINK_CHANNEL_ID_FIELD_NAME)
                ->setCode($constraint::PAY_BY_LINK_CHANNEL_ID_AVAILABLE_ERROR)
                ->addViolation()
            ;

            return;
        }

        if (false === $channel->getAvailable()) {
            $this->context->buildViolation($constraint->availableMessage)
                ->atPath(self::PAY_BY_LINK_CHANNEL_ID_FIELD_NAME)
                ->setCode($constraint::PAY_BY_LINK_CHANNEL_ID_AVAILABLE_ERROR)
                ->addViolation()
            ;

            return;
        }

        if (
            true === $channel->getInstantRedirection() &&
            true === $channel->getOnlinePayment()
        ) {
            return;
        }

        $this->context->buildViolation($constraint->notABankMessage)
            ->atPath(self::PAY_BY_LINK_CHANNEL_ID_FIELD_NAME)
            ->setCode($constraint::PAY_BY_LINK_CHANNEL_ID_AVAILABLE_ERROR)
            ->addViolation()
        ;
    }
}
