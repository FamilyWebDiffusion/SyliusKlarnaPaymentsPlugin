<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Webmozart\Assert\Assert;

final class CaptureAfterShipProcessor
{
    private FactoryInterface $stateMachineFactory;

    public function __construct(FactoryInterface $stateMachineFactory)
    {
        $this->stateMachineFactory = $stateMachineFactory;
    }

    public function processOrder(OrderInterface $order): void
    {
        if ($this->support($order) === true) {
            $lastPayment = $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED);
            if ($lastPayment === null) {
                return;
            }
            $stateMachine = $this->stateMachineFactory->get($lastPayment, PaymentTransitions::GRAPH);
            $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }
    }

    private function support(OrderInterface $order): bool
    {
        try {
            $lastPayment = $order->getLastPayment(PaymentInterface::STATE_AUTHORIZED);
            Assert::notNull($lastPayment);

            Assert::true(Utils::isKlarnaPaymentsSyliusPayment($lastPayment));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
