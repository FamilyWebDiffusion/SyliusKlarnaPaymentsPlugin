<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\StatusAction;
use Payum\Core\Request\Cancel;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

class StatusActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StatusAction::class);
    }

    public function it_marks_request_as_failed(
        GetStatus $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['success' => false]);

        $request->markFailed()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_marks_request_as_authorized(
        GetStatus $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['success' => true, 'status' => KlarnaOrderStatus::STATUS_AUTHORIZED]);

        $request->markAuthorized()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_marks_request_as_pending(
        GetStatus $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['success' => true, 'status' => KlarnaOrderStatus::STATUS_PENDING]);

        $request->markPending()->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_getstatus_request_and_payment_model(
        GetStatus $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_does_not_support_other_request_than_getstatus(
        Cancel $request
    ): void {
        $this->supports($request)->shouldReturn(false);
    }

    public function it_does_dos_support_request_with_other_model_than_payment(
        GetStatus $request
    ): void {
        $request->getFirstModel()->willReturn('something');
        $this->supports($request)->shouldReturn(false);
    }
}
