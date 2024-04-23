<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;


use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\AuthorizeAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Request\Authorize;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthorizeActionSpec extends ObjectBehavior
{
    public function let(RequestStack $requestStack): void
    {
        $this->beConstructedWith($requestStack);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AuthorizeAction::class);
    }

    public function it_implements_action_and_api_aware_interfaces(): void
    {
        $this->shouldHaveType(ActionInterface::class);
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    public function it_execute_and_create_klarna_order_if_authorize_token_exist(
        Authorize $request,
        PaymentInterface $payment,
        KlarnaPaymentsApiClientInterface $api
    ): void {
        $request->getModel()->willReturn($payment);
        $this->setApi($api);
        $payment->getAuthorizationToken()->willReturn('TOKEN');

        $api->createNewKlarnaOrder($payment, 'TOKEN')->willReturn(['details' => 'bla bla'])->shouldBeCalled();
        $payment->setDetails(['details' => 'bla bla'])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_execute_and_does_not_create_klarna_order_if_no_authorize_token(
        Authorize $request,
        PaymentInterface $payment,
        KlarnaPaymentsApiClientInterface $api
    ): void {
        $request->getModel()->willReturn($payment);
        $this->setApi($api);
        $payment->getAuthorizationToken()->willReturn(null);

        $api->createNewKlarnaOrder($payment, '')->shouldNotBeCalled();
        $payment->setDetails(['success' => false])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_authorize_request_and_payment_model(
        Authorize $request,
        PaymentInterface $payment
    ): void {
        $request->getModel()->willReturn($payment);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_does_not_support_other_request_than_authorize(
        GetStatus $request
    ): void {
        $this->supports($request)->shouldReturn(false);
    }

    public function it_does_dos_support_request_with_other_model_than_payment(
        Authorize $request
    ): void {
        $request->getModel()->willReturn('something');
        $this->supports($request)->shouldReturn(false);
    }
}
