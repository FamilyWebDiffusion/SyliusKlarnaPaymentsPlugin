<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaOrderStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        $details = $payment->getDetails();

        if (!$details['success']) {
            $request->markFailed();

            return;
        }

        if ($details['status'] === KlarnaOrderStatus::STATUS_AUTHORIZED) {
            $request->markAuthorized();

            return;
        }

        if ($details['status'] === KlarnaOrderStatus::STATUS_PENDING) {
            $request->markPending();

            return;
        }

        if ($details['status'] === KlarnaOrderStatus::STATUS_CAPTURED) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }

    public function supports($request)
    {
        return
            $request instanceof GetStatus &&
            $request->getFirstModel() instanceof PaymentInterface;
    }
}
