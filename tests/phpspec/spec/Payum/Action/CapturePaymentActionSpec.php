<?php

namespace spec\FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action\CapturePaymentAction;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;


class CapturePaymentActionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CapturePaymentAction::class);
    }

    public function it_implements_action_and_api_aware_interfaces(): void
    {
        $this->shouldHaveType(ActionInterface::class);
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    public function it_execute(
        Capture $request,
        PaymentInterface $payment,
        KlarnaPaymentsApiClientInterface $api,
        GatewayInterface $gateway,
    ): void {
        $request->getFirstModel()->willReturn($payment);
        $payment->getDetails()->willReturn(['order_id' => 'BJ2XWM06', 'status' => KlarnaOrderStatus::STATUS_AUTHORIZED]);

        $this->setApi($api);
        $this->setGateway($gateway);

        $api->captureAllApprovedKlarnaOrder($payment)->willReturn( ["order_id" => "BJ2XWM06", "status" => "authorized"])->shouldBeCalled();

        $payment->setDetails( ["order_id" => "BJ2XWM06", "status" => "authorized"])->shouldBeCalled();
        $gateway->execute(new GetStatus($payment->getWrappedObject()))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_capture_request_and_payment_model(
        Capture $request,
        PaymentInterface $payment
    ): void {
        $request->getFirstModel()->willReturn($payment);

        $this->supports($request)->shouldReturn(true);
    }

    public function it_does_not_support_other_request_than_capture(
        GetStatus $request
    ): void {
        $this->supports($request)->shouldReturn(false);
    }

    public function it_does_dos_support_request_with_other_model_than_payment(
        Capture $request
    ): void {
        $request->getFirstModel()->willReturn('something');
        $this->supports($request)->shouldReturn(false);
    }
}
