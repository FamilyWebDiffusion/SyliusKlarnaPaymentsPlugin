<?php

declare(strict_types=1);

namespace FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum;

use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Entity\KlarnaPaymentsSession;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Exception\KlarnaRequestException;
use FamilyWebDiffusion\SyliusKlarnaPaymentsPlugin\Payum\Gateway\KlarnaGatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface KlarnaPaymentsApiClientInterface
{
    /**
     * @param array<string, string|array> $config
     */
    public function initialize(array $config): void;

    public function getKlarnaPaymentGatewayConfig(): KlarnaGatewayConfigInterface;

    public function getKlarnaPaymentsApiUrl(): string;

    public function getKlarnaOrderManagementApiUrl(): string;

    /**
     * @throws KlarnaRequestException
     */
    public function createAnonymousKlarnaPaymentSession(PaymentInterface $payment): KlarnaPaymentsSession;

    /**
     * @throws KlarnaRequestException
     */
    public function updateAnonymousKlarnaPaymentSession(PaymentInterface $payment, string $klarnaPaymentsSessionId): void;

    public function updateReturnUrlOnKlarnaPaymentSession(string $targetUrl, string $klarnaPaymentsSessionId): void;

    /**
     * @throws KlarnaRequestException
     */
    public function getKlarnaPaymentSession(string $klarnaPaymentsSessionId): KlarnaPaymentsSession;

    public function cancelKlarnaAuthorization(string $authorizationToken): bool;

    /**
     * @return array<string, array|bool|int|string>
     */
    public function createNewKlarnaOrder(PaymentInterface $payment, string $authorizationToken): array;

    /**
     * @return array<string, array|bool|int|string>
     */
    public function cancelKlarnaOrder(PaymentInterface $payment): array;

    /**
     * @return array<string, array|bool|int|string>
     */
    public function captureAllApprovedKlarnaOrder(PaymentInterface $payment): array;

    /**
     * @return array<string, array|bool|int|string>
     */
    public function refundAllKlarnaOrder(PaymentInterface $payment): array;
}
