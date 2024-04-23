<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\EventListener;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\EventListener\PaymentFailedEventListener;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;


class PaymentFailedEventListenerSpec extends ObjectBehavior
{
    public function let(
        KlarnaPaymentsApiClientInterface $apiClient
    ): void {
        $this->beConstructedWith($apiClient);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentFailedEventListener::class);
    }

    public function it_cancel_existing_klarna_authorization_token_if_payment_failed(
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        KlarnaPaymentsSession $klarnaPaymentsSession,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('failed');
        $payment->getOrder()->willReturn($order);
        $order->getState()->willReturn('new');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn(['factory_name' => 'klarna_payments']);
        $payment->getAuthorizationToken()->willReturn(null);
        $order->getKlarnaSessionId()->willReturn('klarna_session_id');
        $apiClient->getKlarnaPaymentSession('klarna_session_id')->shouldBeCalled()->willReturn($klarnaPaymentsSession);
        $klarnaPaymentsSession->getAuthorizationToken()->willReturn('authorization_token');

        $apiClient->cancelKlarnaAuthorization('authorization_token')->shouldBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }

    public function it_does_not_cancel_klarna_authorization_token_if_payment_dont_failed(
        PaymentInterface $payment,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('processing');
        $payment->getOrder()->shouldNotBeCalled();

        $apiClient->cancelKlarnaAuthorization()->shouldNotBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }

    public function it_does_not_cancel_klarna_authorization_token_if_order_is_fulfilled(
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        KlarnaPaymentsSession $klarnaPaymentsSession,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('failed');
        $payment->getOrder()->willReturn($order);
        $order->getState()->willReturn('fulfilled');
        $payment->getMethod()->shouldNotBeCalled();

        $apiClient->cancelKlarnaAuthorization()->shouldNotBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }

    public function it_does_not_cancel_klarna_authorization_token_if_it_is_not_klarna_payment(
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        KlarnaPaymentsSession $klarnaPaymentsSession,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('failed');
        $payment->getOrder()->willReturn($order);
        $order->getState()->willReturn('new');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn(['factory_name' => 'offline']);
        $payment->getAuthorizationToken()->shouldNotBeCalled();

        $apiClient->cancelKlarnaAuthorization()->shouldNotBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }

    public function it_does_not_cancel_klarna_authorization_token_if_it_there_is_no_session_id(
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        KlarnaPaymentsSession $klarnaPaymentsSession,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('failed');
        $payment->getOrder()->willReturn($order);
        $order->getState()->willReturn('new');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn(['factory_name' => 'klarna_payments']);
        $payment->getAuthorizationToken()->willReturn(null);
        $order->getKlarnaSessionId()->willReturn(null);
        $apiClient->getKlarnaPaymentSession()->shouldnotBeCalled();

        $apiClient->cancelKlarnaAuthorization()->shouldNotBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }

    public function it_does_not_cancel_klarna_authorization_token_if_it_there_is_no_authorization_token(
        PaymentInterface $payment,
        OrderInterface $order,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        KlarnaPaymentsSession $klarnaPaymentsSession,
        KlarnaPaymentsApiClientInterface $apiClient
    ):void {
        $payment->getState()->willReturn('failed');
        $payment->getOrder()->willReturn($order);
        $order->getState()->willReturn('new');
        $payment->getMethod()->willReturn($paymentMethod);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getConfig()->willReturn(['factory_name' => 'klarna_payments']);
        $payment->getAuthorizationToken()->willReturn(null);
        $order->getKlarnaSessionId()->willReturn('klarna_session_id');
        $apiClient->getKlarnaPaymentSession('klarna_session_id')->shouldBeCalled()->willReturn($klarnaPaymentsSession);
        $klarnaPaymentsSession->getAuthorizationToken()->willReturn(null);

        $apiClient->cancelKlarnaAuthorization()->shouldNotBeCalled();

        $this->cancelExistingKlarnaAuthorizationToken($payment);
    }
}
