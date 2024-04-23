<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterDataTransformersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('familywebdiffusion_sylius_klarna_payments_plugin.registry.payment_data_builder')) {
            return;
        }

        $registry = $container->getDefinition('familywebdiffusion_sylius_klarna_payments_plugin.registry.payment_data_builder');

        foreach ($container->findTaggedServiceIds('familywebdiffusion_sylius_klarna_payments_plugin.payment_data_builder') as $id => $attributes) {
            if (!isset($attributes[0]['priority'])) {
                throw new \InvalidArgumentException('Tagged data transformer needs to have a `priority` attribute.');
            }

            $priority = (int) $attributes[0]['priority'];
            $registry->addMethodCall('register', [new Reference($id), $priority]);
        }
    }
}
