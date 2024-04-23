<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Form\Type;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class KlarnaPaymentsGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_API_ZONE,
                ChoiceType::class,
                [
                    'label' => 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.api_zone',
                    'required' => true,
                    'placeholder' => false,
                    'choices' => [
                        'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.server_europe' => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_EUROPE,
                        'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.server_north_america' => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_NORTH_AMERICA,
                        'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.server_oceania' => KlarnaGatewayConfigInterface::CONFIG_API_SERVER_OCEANIA,
                        ],
                ],
            )
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_API_SANDBOX,
                CheckboxType::class,
                [
                'label' => 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.sandbox',
                'required' => false,
                ],
            )
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_API_USERNAME,
                TextType::class,
                [
                'label' => 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.api_username',
                'constraints' => [
                    new NotBlank(),
                    ],
                ],
            )
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_API_PASSWORD,
                TextType::class,
                [
                'label' => 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.api_password',
                'constraints' => [
                    new NotBlank(),
                    ],
                ],
            )
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD,
                ChoiceType::class,
                [
                    'label' => 'sylius.form.gateway_configuration.familywebdiffusion_klarna_payments.enabled_payment_method',
                    'required' => true,
                    'placeholder' => false,
                    'choices' => KlarnaGatewayConfigInterface::PAYMENT_METHOD_IDS,
                ],
            )
            ->add(
                KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME,
                HiddenType::class,
                [
                    'data' => KlarnaPaymentsGatewayFactory::NAME,
                ],
            )
            // Only make authorize for klarna payments
            ->add(
                'use_authorize',
                HiddenType::class,
                [
                    'data' => 1,
                ],
            )
        ;
    }
}
