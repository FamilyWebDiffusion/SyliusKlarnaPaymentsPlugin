<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaPaymentsGatewayFactory;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\StateMachine\CancelAuthorizedOrderProcessor;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Cancel;
use Payum\Core\Security\TokenFactoryInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CancelAuthorizedOrderProcessorSpec extends ObjectBehavior
{
    public function let(
        Payum $payum
    ): void {
        $this->beConstructedWith($payum);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CancelAuthorizedOrderProcessor::class);
    }

    public function it_executes_cancel_action_on_payment(
        PaymentInterface $payment,
        PaymentMethodInterface $method,
        GatewayConfigInterface $gatewayConfig,
        TokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        GatewayInterface $gateway,
        Payum $payum
    ): void {
        $payment->getMethod()->willReturn($method);
        $payment->getState()->willReturn(PaymentInterface::STATE_AUTHORIZED);
        $method->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getGatewayName()->willReturn('klarna_payment');
        $gatewayConfig->getConfig()->willReturn([
            KlarnaGatewayConfigInterface::CONFIG_FACTORY_NAME => KlarnaPaymentsGatewayFactory::NAME
        ]);
        $payum->getGateway('klarna_payment')->willReturn($gateway);
        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory->createToken('klarna_payment', $payment,'sylius_shop_order_after_pay')->willReturn($token);
        $request = new Cancel($token->getWrappedObject());

        $gateway->execute($request)->shouldBeCalled();

        $this->processPayment($payment);
    }
}
