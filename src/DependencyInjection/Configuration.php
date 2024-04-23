<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DependencyInjection;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('family_web_diffusion_sylius_klarna_payments_plugin');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_TEST)
                    ->children()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api.playground.klarna.com')
                        ->end()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api-na.playground.klarna.com/')
                        ->end()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api-oc.playground.klarna.com/')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_PROD)
                    ->children()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api.klarna.com/')
                        ->end()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api-na.klarna.com/')
                        ->end()
                        ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('https://api-oc.klarna.com/')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_ENDPOINT)
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(KlarnaGatewayConfigInterface::CONFIG_API_ORDERMANAGMENT_ENDPOINT)
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                    ->integerNode(KlarnaGatewayConfigInterface::CONFIG_CLIENT_RETRY)
                    ->isRequired()
                    ->min(1)->max(6)
                ->end()
                ->booleanNode(KlarnaGatewayConfigInterface::CONFIG_DISPLAY_BIRTHDAY)
                    ->defaultTrue()
                ->end()
        ;

        return $treeBuilder;
    }
}
