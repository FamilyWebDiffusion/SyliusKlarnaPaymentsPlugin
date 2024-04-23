<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Request\AuthorizeByCallback;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\OrderPaymentStates;

class AuthorizeByCallbackAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var OrderInterface $order */
        $order = $request->getModel();
        $response = $request->getResponse();
        $orderKlarnaSessionId = $order->getKlarnaSessionId();
        $callbackKlarnaSessionId = $response['session_id'];
        $klarnaAuthorizationToken = $response['authorization_token'];

        if ($orderKlarnaSessionId !== $callbackKlarnaSessionId) {
            return;
        }

        if ($klarnaAuthorizationToken === '') {
            return;
        }

        // it's the good order, now validate klarna payment (if payment is still pending)
        if (!\in_array(
            $order->getPaymentState(),
            [OrderPaymentStates::STATE_CART, OrderPaymentStates::STATE_AWAITING_PAYMENT],
            true,
        )) {
            return;
        }

        // work on last payment
        /** @var ?PaymentInterface $lastPayment */
        $lastPayment = $order->getLastPayment();
        if ($lastPayment === null) {
            return;
        }

        $lastPayment->setAuthorizationToken($klarnaAuthorizationToken);
    }

    public function supports($request): bool
    {
        if (!$request instanceof AuthorizeByCallback) {
            return false;
        }

        $response = $request->getResponse();

        return
            $request->getModel() instanceof OrderInterface &&
            $response !== [] &&
            isset($response['authorization_token'], $response['session_id'])
        ;
    }
}
