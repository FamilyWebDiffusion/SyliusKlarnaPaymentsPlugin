<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\EventListener;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface as KlarnaOrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\PaymentInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception\KlarnaRequestException;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Sylius\Component\Core\Model\OrderInterface;

final class PaymentFailedEventListener
{
    private KlarnaPaymentsApiClientInterface $apiClient;

    public function __construct(KlarnaPaymentsApiClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * This listener cancel potential existing authorization_token if it already exists
     */
    public function cancelExistingKlarnaAuthorizationToken(PaymentInterface $payment): void
    {
        if ($payment->getState() !== PaymentInterface::STATE_FAILED) {
            return;
        }

        if ($payment->getOrder()?->getState() === OrderInterface::STATE_FULFILLED) {
            return;
        }

        if (Utils::isKlarnaPaymentsSyliusPayment($payment) === false) {
            return;
        }

        /** if payment already got AuthorizationToken, that's mean it succeeded */
        if ($payment->getAuthorizationToken() !== null) {
            return;
        }

        /** @var ?KlarnaOrderInterface $order */
        $order = $payment->getOrder();
        $klarnaSessionId = $order?->getKlarnaSessionId();

        if ($klarnaSessionId === null) {
            return;
        }

        try {
            $klarnaPaymentSession = $this->apiClient->getKlarnaPaymentSession($klarnaSessionId);
            $authorizationToken = $klarnaPaymentSession->getAuthorizationToken();

            if ($authorizationToken === null) {
                return;
            }

            $this->apiClient->cancelKlarnaAuthorization($authorizationToken);
        } catch (KlarnaRequestException $e) {
        }
    }
}
