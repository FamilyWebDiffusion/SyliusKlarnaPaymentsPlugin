<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;

final class RefundOrderProcessor extends AbstractOrderProcessor
{
    public function processPayment(PaymentInterface $payment): void
    {
        if ($payment->getState() !== PaymentInterface::STATE_COMPLETED) {
            return;
        }

        if (!Utils::isKlarnaPaymentsSyliusPayment($payment)) {
            return;
        }

        $gatewayName = $this->getGatewayNameFromPayment($payment);
        if ($gatewayName === null) {
            return;
        }

        $gateway = $this->payum->getGateway($gatewayName);
        $tokenFactory = $this->payum->getTokenFactory();

        $token = $tokenFactory->createToken($gatewayName, $payment, 'sylius_shop_order_after_pay');
        $refundRequest = new Refund($token);

        $gateway->execute($refundRequest);
    }
}
