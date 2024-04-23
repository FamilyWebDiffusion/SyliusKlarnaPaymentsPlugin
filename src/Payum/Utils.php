<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;

class Utils
{
    // Since getFactoryName is deprecated
    // factory_name should be set in KlarnaPaymentsGatewayConfigurationType
    public static function isKlarnaPaymentsGateway(GatewayConfigInterface $gatewayConfig): bool
    {
        $config = $gatewayConfig->getConfig();
        if (!isset($config[KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME])) {
            return false;
        }

        return $config[KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME] === KlarnaPaymentsGatewayFactory::NAME;
    }

    public static function isKlarnaPaymentsSyliusPayment(SyliusPaymentInterface $payment): bool
    {
        $paymentMethod = $payment->getMethod();
        if (!$paymentMethod instanceof PaymentMethodInterface) {
            return false;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if ($gatewayConfig === null) {
            return false;
        }

        return self::isKlarnaPaymentsGateway($gatewayConfig);
    }
}
