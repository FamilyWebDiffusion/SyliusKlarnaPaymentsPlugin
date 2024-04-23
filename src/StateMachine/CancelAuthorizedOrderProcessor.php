<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine;

use Payum\Core\Request\Cancel;
use Sylius\Component\Core\Model\PaymentInterface;

final class CancelAuthorizedOrderProcessor extends AbstractOrderProcessor
{
    public function processPayment(PaymentInterface $payment): void
    {
        if (!$this->isKlarnaAuthorizedPayment($payment)) {
            return;
        }

        $gatewayName = $this->getGatewayNameFromPayment($payment);
        if ($gatewayName === null) {
            return;
        }

        $gateway = $this->payum->getGateway($gatewayName);
        $tokenFactory = $this->payum->getTokenFactory();

        $token = $tokenFactory->createToken($gatewayName, $payment, 'sylius_shop_order_after_pay');
        $cancelRequest = new Cancel($token);

        $gateway->execute($cancelRequest);
    }
}
