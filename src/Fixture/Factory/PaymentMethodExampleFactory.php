<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Fixture\Factory;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageAwareInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\PaymentMethodExampleFactory as BasePaymentMethodExampleFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class PaymentMethodExampleFactory extends BasePaymentMethodExampleFactory
{
    public function __construct(
        PaymentMethodFactoryInterface $paymentMethodFactory,
        RepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
    ) {
        /** @phpstan-ignore-next-line */
        parent::__construct($paymentMethodFactory, $localeRepository, $channelRepository);
    }

    public function create(array $options = []): PaymentMethodInterface
    {
        $paymentMethod = parent::create($options);

        if (isset($options['defaultImageUrl']) && $paymentMethod instanceof PaymentMethodImageAwareInterface) {
            $paymentMethod->setDefaultImageUrl($options['defaultImageUrl']);
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        return $paymentMethod;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('defaultImageUrl', null)
            ->setAllowedTypes('defaultImageUrl', ['null', 'string'])
        ;
    }
}
