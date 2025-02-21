<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusTpayPlugin\DependencyInjection;

use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAlias;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Entity\BlikAliasInterface;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Factory\BlikAliasFactory;
use CommerceWeavers\SyliusTpayPlugin\BlikPayment\Repository\BlikAliasRepository;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Entity\CreditCard;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Entity\CreditCardInterface;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Factory\CreditCardFactory;
use CommerceWeavers\SyliusTpayPlugin\CardPayment\Repository\CreditCardRepository;
use CommerceWeavers\SyliusTpayPlugin\Entity\PaymentMethodImage;
use CommerceWeavers\SyliusTpayPlugin\Model\PaymentMethodImageInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('commerce_weavers_sylius_tpay');
        $rootNode = $treeBuilder->getRootNode();

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('blik_alias')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(BlikAlias::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(BlikAliasInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(BlikAliasFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(BlikAliasRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('credit_card')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(CreditCard::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(CreditCardInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(CreditCardFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(CreditCardRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('payment_method_image')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(PaymentMethodImage::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(PaymentMethodImageInterface::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
