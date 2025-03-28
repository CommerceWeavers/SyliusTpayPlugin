<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\Fixture;

use Doctrine\Persistence\ObjectManager;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\PaymentMethodFixture as DecoratedPaymentMethodFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class PaymentMethodFixture extends DecoratedPaymentMethodFixture
{
    public function __construct(
        ObjectManager $objectManager,
        ExampleFactoryInterface $exampleFactory,
    ) {
        parent::__construct($objectManager, $exampleFactory);
    }

    public function getName(): string
    {
        return parent::getName();
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        parent::configureResourceNode($resourceNode);

        $resourceNode->children()
            ->scalarNode('defaultImageUrl')->end()
        ;
    }
}
