<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;

final class RefundAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

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

        if (!isset($details['order_id'])) {
            return;
        }

        $return = $this->api->refundAllKlarnaOrder($payment);

        $details = \array_merge($details, $return);
        $payment->setDetails($details);
    }

    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getFirstModel() instanceof PaymentInterface;
    }
}
