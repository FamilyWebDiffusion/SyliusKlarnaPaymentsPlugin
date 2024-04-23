<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\AuthorizeByCallbackAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Request\AuthorizeByCallback;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\OrderPaymentStates;

class AuthorizeByCallbackActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AuthorizeByCallbackAction::class);
    }

    public function it_execute(
        AuthorizeByCallback $request,
        OrderInterface $order,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
    ): void
    {
        $request->getModel()->willReturn($order);
        $request->getResponse()->willReturn(['authorization_token' => 'TOKEN', 'session_id' => 'SESSION_ID']);

        $order->getKlarnaSessionId()->willReturn('SESSION_ID');
        $order->getPaymentState()->willReturn(OrderPaymentStates::STATE_CART);
        $order->getLastPayment()->willReturn($payment);
        $payment->getMethod()->willReturn($paymentMethod);

        $payment->setAuthorizationToken('TOKEN')->shouldBeCalled();

        $this->execute($request);
    }

    public function it_does_not_execute_if_not_good_order(
        AuthorizeByCallback $request,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void
    {
        $request->getModel()->willReturn($order);
        $request->getResponse()->willReturn(['authorization_token' => 'TOKEN', 'session_id' => 'SESSION_ID']);

        $order->getKlarnaSessionId()->willReturn('ANOTHER_SESSION_ID');

        $payment->setAuthorizationToken('TOKEN')->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_does_not_execute_if_not_good_state(
        AuthorizeByCallback $request,
        OrderInterface $order,
        PaymentInterface $payment,
    ): void
    {
        $request->getModel()->willReturn($order);
        $request->getResponse()->willReturn(['authorization_token' => 'TOKEN', 'session_id' => 'SESSION_ID']);

        $order->getKlarnaSessionId()->willReturn('SESSION_ID');
        $order->getPaymentState()->willReturn(OrderPaymentStates::STATE_CANCELLED);

        $payment->setAuthorizationToken('TOKEN')->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_supports_authorize_callback_request_and_order_model(
        AuthorizeByCallback $request,
        OrderInterface $order,
    ): void {
        $request->getModel()->willReturn($order);
        $request->getResponse()->willReturn(['authorization_token' => 'TOKEN', 'session_id' => 'SESSION_ID']);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_supports_only_request_with_auth_token_and_session_id(
            AuthorizeByCallback $request,
            OrderInterface $order,
        ): void {
        $request->getModel()->willReturn($order);
        $request->getResponse()->willReturn(['authorization_token' => 'TOKEN', 'random' => 'other']);
         $this->supports($request)->shouldReturn(false);
    }

}
