<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

abstract class AbstractOrderProcessor
{
    protected Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    abstract public function processPayment(PaymentInterface $payment): void;

    protected function getGatewayNameFromPayment(PaymentInterface $payment): ?string
    {
        $method = $payment->getMethod();
        if ($method === null || !$method instanceof PaymentMethodInterface) {
            return null;
        }
        $config = $method->getGatewayConfig();

        if ($config === null) {
            return null;
        }

        return $config->getGatewayName();
    }

    protected function isKlarnaAuthorizedPayment(PaymentInterface $payment): bool
    {
        try {
            Assert::same($payment->getState(), PaymentInterface::STATE_AUTHORIZED);

            Assert::true(Utils::isKlarnaPaymentsSyliusPayment($payment));

            Assert::notNull($this->getGatewayNameFromPayment($payment));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
