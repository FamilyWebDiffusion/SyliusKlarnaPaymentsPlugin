<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DependencyInjection;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Client\RetryableHttpClientFactory;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class FamilyWebDiffusionSyliusKlarnaPaymentsExtension extends Extension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    /** @psalm-suppress UnusedVariable */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.yml');

        //inject config into apiClient et youp!
        $klarnaPaymentApiClientDefinition = $container->getDefinition(KlarnaPaymentsApiClient::class);
        $klarnaPaymentApiClientDefinition->addArgument($config);

        //inject max retry to client factory
        $klarnaClientRetryableFactory = $container->getDefinition(RetryableHttpClientFactory::class);
        $klarnaClientRetryableFactory->addArgument($config[KlarnaGatewayConfigInterface::CONFIG_CLIENT_RETRY]);

        //inject if birthday is send to klarna
        $customerDataTransformer = $container->getDefinition('familywebdiffusion_sylius_klarna_payments_plugin.payment_data_builder.customer');
        $customerDataTransformer->addArgument($config[KlarnaGatewayConfigInterface::CONFIG_DISPLAY_BIRTHDAY]);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    protected function getMigrationsNamespace(): string
    {
        return 'FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@FamilyWebDiffusionSyliusKlarnaPaymentsPlugin/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return ['Sylius\Bundle\CoreBundle\Migrations'];
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }
}
