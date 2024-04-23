<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Twig;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\DataTransformer\PaymentDataTransformer;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\OrderInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Helper\SyliusKlarnaPaymentMethodHelper;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClient;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApiClientInterface;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Utils;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface as CorePaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class KlarnaOrderExtension extends AbstractExtension
{
    private PaymentDataTransformer $paymentDataTransformer;

    private KlarnaPaymentsApiClientInterface $klarnaPaymentsApiClient;

    private SyliusKlarnaPaymentMethodHelper $syliusKlarnaPaymentMethodHelper;

    public function __construct(
        PaymentDataTransformer $paymentDataTransformer,
        ContainerInterface $container,
        SyliusKlarnaPaymentMethodHelper $syliusKlarnaPaymentMethodHelper,
    ) {
        $this->paymentDataTransformer = $paymentDataTransformer;
        $this->syliusKlarnaPaymentMethodHelper = $syliusKlarnaPaymentMethodHelper;
        $client = $container->get(KlarnaPaymentsApiClient::class);
        if ($client instanceof KlarnaPaymentsApiClientInterface) {
            $this->klarnaPaymentsApiClient = $client;
        }
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getCompleteKlarnaOrder', [$this, 'getCompleteKlarnaOrder']),
            new TwigFunction('getKlarnaPaymentSession', [$this, 'getKlarnaPaymentSession']),
            new TwigFunction('getAlreadyInitializedKlarnaPaymentSession', [$this, 'getAlreadyInitializedKlarnaPaymentSession']),
            new TwigFunction('getKlarnaMethods', [$this, 'getKlarnaMethods']),
            ];
    }

    public function getCompleteKlarnaOrder(PaymentInterface $payment): array
    {
        return $this->paymentDataTransformer->transform([], $payment);
    }

    public function getKlarnaPaymentSession(OrderInterface $order): ?KlarnaPaymentsSession
    {
        $klarnaSessionId = $order->getKlarnaSessionId();
        if ($klarnaSessionId === null) {
            return null;
        }
        $payment = $order->getLastPayment();
        if ($payment === null) {
            return null;
        }

        $method = $payment->getMethod();
        if ($method === null || !Utils::isKlarnaPaymentsSyliusPayment($payment)) {
            return null;
        }

        if (!$this->initializeKlarnaApiForMethod($method)) {
            return null;
        }

        return $this->klarnaPaymentsApiClient->getKlarnaPaymentSession($klarnaSessionId);
    }

    public function getAlreadyInitializedKlarnaPaymentSession(OrderInterface $order): ?KlarnaPaymentsSession
    {
        $klarnaSessionId = $order->getKlarnaSessionId();
        if ($klarnaSessionId === null) {
            return null;
        }

        return $this->klarnaPaymentsApiClient->getKlarnaPaymentSession($klarnaSessionId);
    }

    public function getKlarnaMethods(OrderInterface $order): ?array
    {
        return $this->syliusKlarnaPaymentMethodHelper->getKlarnaMethods($order);
    }

    private function initializeKlarnaApiForMethod(PaymentMethodInterface $paymentMethod): bool
    {
        if (!$paymentMethod instanceof CorePaymentMethodInterface) {
            return false;
        }

        try {
            $gatewayConfig = $paymentMethod->getGatewayConfig();
            if ($gatewayConfig === null) {
                return false;
            }
            $this->klarnaPaymentsApiClient->initialize($gatewayConfig->getConfig());
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
