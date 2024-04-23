<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\RefundAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Request\Refund;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

class RefundActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RefundAction::class);
    }

    public function it_implements_action_and_api_aware_interface(): void
    {
        $this->shouldHaveType(RefundAction::class);
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    public function it_executes(
        Refund $request,
        PaymentInterface $payment,
        KlarnaPaymentsApiClientInterface $api
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['order_id' => 'BJ2XWM06', 'status' => KlarnaOrderStatus::STATUS_AUTHORIZED]);

        $this->setApi($api);

        $api->refundAllKlarnaOrder($payment)->willReturn( ["order_id" => "BJ2XWM06", "status" => "authorized"])->shouldBeCalled();
        $payment->setDetails( ["order_id" => "BJ2XWM06", "status" => "authorized"])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_cancel_request_and_payment_model(
        Refund $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_does_not_support_other_request_than_refund(
        GetStatus $request
    ): void {
        $this->supports($request)->shouldReturn(false);
    }

    public function it_does_dos_support_request_with_other_model_than_payment(
        Refund $request
    ): void {
        $request->getFirstModel()->willReturn('something');
        $this->supports($request)->shouldReturn(false);
    }
}
