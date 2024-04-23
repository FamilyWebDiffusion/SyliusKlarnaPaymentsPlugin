<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Cancel Authorized Klarna Payments
 * Class CancelAction
 */
final class CancelAction implements ActionInterface, ApiAwareInterface
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
        if (!isset($details['order_id']) || $details['status'] !== KlarnaOrderStatus::STATUS_AUTHORIZED) {
            return;
        }

        $return = $this->api->cancelKlarnaOrder($payment);
        $details = \array_merge($details, $return);
        $payment->setDetails($details);
    }

    public function supports($request)
    {
        return
            $request instanceof Cancel &&
            $request->getFirstModel() instanceof PaymentInterface;
    }
}
