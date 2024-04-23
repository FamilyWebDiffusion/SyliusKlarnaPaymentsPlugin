<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Helper;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

final class SyliusKlarnaPaymentMethodHelper
{
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getKlarnaMethods(OrderInterface $order): ?array
    {
        $klarnaMethods = [];
        $orderChannel = $order->getChannel();
        if ($orderChannel === null) {
            return $klarnaMethods;
        }

        $paymentMethods = $this->paymentMethodRepository->findBy(['enabled' => true]);
        /** @var PaymentMethodInterface $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            $paymentCode = $paymentMethod->getCode();
            if ($paymentCode === null) {
                continue;
            }
            $channels = $paymentMethod->getChannels()->toArray();
            if (!\in_array($orderChannel, $channels, true)) {
                continue;
            }
            $gatewayConfig = $paymentMethod->getGatewayConfig();

            if ($gatewayConfig === null || !Utils::isKlarnaPaymentsGateway($gatewayConfig)) {
                continue;
            }
            $config = $gatewayConfig->getConfig();

            if (isset($config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD])) {
                $klarnaMethods[$paymentCode] = $config[KlarnaGatewayConfigInterface::CONFIG_API_PAYMENT_METHOD];
            }
        }

        return $klarnaMethods;
    }
}
