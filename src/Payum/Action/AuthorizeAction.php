<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Action;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuthorizeAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->apiClass = KlarnaPaymentsApiClientInterface::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $klarnaAuthorizationToken = $payment->getAuthorizationToken();

        if ($klarnaAuthorizationToken === null) {
            $klarnaOrder = ['success' => false];
        } else {
            $klarnaOrder = $this->api->createNewKlarnaOrder($payment, $klarnaAuthorizationToken);
        }

        $payment->setDetails($klarnaOrder);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof PaymentInterface;
    }
}
