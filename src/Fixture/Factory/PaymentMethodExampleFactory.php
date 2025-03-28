<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Fixture\Factory;

use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageAwareInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\PaymentMethodExampleFactory as BasePaymentMethodExampleFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface as DoctrineRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class PaymentMethodExampleFactory extends BasePaymentMethodExampleFactory
{
    public const TPAY_BASED_PAYMENT_METHOD_PREFIX = 'tpay';

    public function __construct(
        private readonly CypherInterface $cypher,
        PaymentMethodFactoryInterface $paymentMethodFactory,
        RepositoryInterface|DoctrineRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
    ) {
        parent::__construct($paymentMethodFactory, $localeRepository, $channelRepository);
    }

    public function create(array $options = []): PaymentMethodInterface
    {
        /** @var PaymentMethodInterface|mixed $paymentMethod */
        $paymentMethod = parent::create($options);

        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        if (isset($options['defaultImageUrl']) && $paymentMethod instanceof PaymentMethodImageAwareInterface) {
            $paymentMethod->setDefaultImageUrl($options['defaultImageUrl']);
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        if (!str_starts_with($gatewayConfig->getGatewayName(), self::TPAY_BASED_PAYMENT_METHOD_PREFIX)) {
            return $paymentMethod;
        }

        if ($gatewayConfig instanceof CryptedInterface) {
            $gatewayConfig->encrypt($this->cypher);
        }

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
