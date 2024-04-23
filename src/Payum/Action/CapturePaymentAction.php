<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

class CapturePaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = KlarnaPaymentsApiClientInterface::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        $details = $payment->getDetails();

        if (!isset($details['order_id']) || $details['status'] !== KlarnaOrderStatus::STATUS_AUTHORIZED) {
            return;
        }

        $return = $this->api->captureAllApprovedKlarnaOrder($payment);
        $details = \array_merge($details, $return);
        $payment->setDetails($details);

        $this->gateway->execute(new GetStatus($payment));
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
